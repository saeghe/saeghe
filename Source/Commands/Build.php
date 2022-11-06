<?php

namespace Saeghe\Saeghe\Commands\Build;

use Saeghe\Cli\IO\Write;
use Saeghe\Saeghe\Config;
use Saeghe\Saeghe\Meta;
use Saeghe\Saeghe\Package;
use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\FileManager\Directory;
use Saeghe\Saeghe\FileManager\File;
use Saeghe\Saeghe\FileManager\FileType\Json;
use Saeghe\Saeghe\PhpFile;
use Saeghe\Saeghe\Project;
use Saeghe\Saeghe\DataType\Str;

$autoloads = [];

function run(Project $project)
{
    umask(0);

    $config = File\exists($project->config_file_path->to_string())
        ? Config::from_array(Json\to_array($project->config_file_path->to_string()))
        : Config::init();

    $meta = File\exists($project->config_lock_file_path->to_string())
        ? Meta::from_array(Json\to_array($project->config_lock_file_path->to_string()))
        : Meta::init();

    Directory\renew_recursive($project->build_root->to_string());

    Directory\exists_or_create($project->build_root->append($config->packages_directory)->to_string());

    $replace_map = make_replace_map($project, $config, $meta);

    foreach ($meta->packages as $package) {
        compile_packages($project, $config, $package, $replace_map);
    }

    compile_project_files($project, $config, $replace_map);

    global $autoloads;
    make_entry_points($project, $config, $replace_map, $autoloads);

    foreach ($meta->packages as $package) {
        add_executables($project, $config, $package, $replace_map, $autoloads);
    }

    clearstatcache();

    Write\success('Build finished successfully.');
}

function make_entry_points(Project $project, Config $config, array $replace_map, array $autoloads): void
{
    foreach ($config->entry_points as $entry_point) {
        add_autoloads($project->build_root->append($entry_point), $replace_map, $autoloads);
    }
}

function add_executables(Project $project, Config $config, Package $package, array $replace_map, array $autoloads): void
{
    $package_config = $package->config($project, $config);
    foreach ($package_config->executables as $link_name => $source) {
        $target = $package->build_root($project, $config)->append($source);
        $link = $project->build_root->append($link_name);
        symlink($target->to_string(), $link->to_string());
        add_autoloads($target, $replace_map, $autoloads);
        File\chmod($target->to_string(), 0774);
    }
}

function compile_packages(Project $project, Config $config, Package $package, array $replace_map): void
{
    Directory\renew_recursive($project->build_root->append("{$config->packages_directory}/{$package->owner}/{$package->repo}")->to_string());

    $files_and_directories = should_compile_files_and_directories_for_package($project, $config, $package);
    $package_config = $package->config($project, $config);
    $package_root = $package->root($project, $config);
    $package_build_root = $package->build_root($project, $config);

    foreach ($files_and_directories as $file_or_directory) {
        compile($package_config, $package_root->append($file_or_directory), $package_build_root->append($file_or_directory), $replace_map);
    }
}

function compile_project_files(Project $project, Config $config, array $replace_map): void
{
    $files_and_directories = should_compile_files_and_directories($project, $config);

    foreach ($files_and_directories as $file_or_directory) {
        compile($config, $project->root->append($file_or_directory), $project->build_root->append($file_or_directory), $replace_map);
    }
}

function compile(Config $config, Address $origin, Address $destination, array $replace_map): void
{
    if (is_dir($origin->to_string())) {
        Directory\preserve_copy($origin->to_string(), $destination->to_string());
        $sub_files_and_directories = Directory\ls_all($origin->to_string());
        foreach ($sub_files_and_directories as $sub_file_or_directory) {
            compile($config, $origin->append($sub_file_or_directory), $destination->append($sub_file_or_directory), $replace_map);
        }

        return;
    }

    if (file_needs_modification($origin, $config)) {
        compile_file($origin, $destination, $replace_map);

        return;
    } else if (is_link($origin->to_string())) {
        $source_link = $origin->parent()->append(readlink($origin->to_string()));
        symlink($source_link->to_string(), $destination->to_string());

        return;
    }

    File\preserve_copy($origin->to_string(), $destination->to_string());
}

function compile_file(Address $origin, Address $destination, array $replace_map): void
{
    $modifiedFile = apply_file_modifications($origin, $replace_map);
    File\create($destination->to_string(), $modifiedFile, File\permission($origin->to_string()));
}

function apply_file_modifications(Address $origin, array $replace_map): string
{
    global $autoloads;

    $content = file_get_contents($origin->to_string());
    $php_file = new PhpFile($content);

    $imports = array_merge(
        $php_file->used_constants(),
        array_keys($php_file->imported_constants()),
        $php_file->used_functions(),
        array_keys($php_file->imported_functions()),
    );
    $autoload = array_merge(
        $php_file->extended_classes(),
        $php_file->implemented_interfaces(),
        $php_file->used_traits(),
        $php_file->used_classes(),
        array_keys($php_file->imported_classes()),
    );

    $require_statements = [];

     array_walk($imports, function ($import) use ($replace_map, &$require_statements) {
        $path = path_finder($replace_map, $import, true);
        if (! $path) {
            $path = path_finder($replace_map, Str\before_last_occurrence($import, '\\'), false);
        }

        $require_statements[$import] = $path;
    });

    $autoload_map = [];
    array_walk($autoload, function ($import) use ($replace_map, &$autoload_map) {
        $path = path_finder($replace_map, $import, false);
        $autoload_map[$import] = $path;
    });

    $autoloads = array_merge(
        $autoloads,
        array_unique(array_filter($autoload_map))
    );

    $require_statements = array_unique(array_filter($require_statements));

    if (count($require_statements)) {
        $require_statements = array_map(fn($path) => "require_once '$path';", $require_statements);

        return add_requires_and_autoload($require_statements, $origin);
    }

    return $content;
}

function path_finder(array $replace_map, string $import, bool $absolute): ?string
{
    $realpath = null;

    foreach ($replace_map as $namespace => $path) {
        if ($absolute) {
            if ($import === $namespace && str_ends_with($path, '.php')) {
                return $path;
            }
        } else {
            if (str_starts_with($import, $namespace)) {
                $pos = strpos($import, $namespace);
                if ($pos !== false) {
                    $realpath = substr_replace($import, $path, $pos, strlen($namespace));
                }
                $realpath = str_replace('\\', '/', $realpath) . '.php';
            }
        }
    }

    return $realpath ? Address::from_string($realpath)->to_string() : null;
}

function add_requires_and_autoload(array $require_statements, Address $file): string
{
    $content = '';

    $requires_added = false;

    foreach (File\lines($file->to_string()) as $line) {
        $content .= $line;

        if (str_starts_with($line, 'namespace')) {
            $requires_added = true;
            if (count($require_statements) > 0) {
                $content .= PHP_EOL;
                $content .= implode(PHP_EOL, $require_statements);
                $content .= PHP_EOL;
            }
        }
    }

    if (! $requires_added) {
        $content = '';
        foreach (File\lines($file->to_string()) as $line) {
            $content .= $line;

            if (! $requires_added && str_starts_with($line, '<?php')) {
                $requires_added = true;
                $content .= PHP_EOL;
                $content .= implode(PHP_EOL, $require_statements);
                $content .= PHP_EOL;
            }
        }
    }

    return $content;
}

function make_replace_map(Project $project, Config $config, Meta $meta): array
{
    $replace_map = [];

    $map_package_namespaces = function (Package $package) use (&$replace_map, $project, $config) {
        $package_config = $package->config($project, $config);
        $package_root = $package->build_root($project, $config);

        foreach ($package_config->map as $namespace => $source) {
            $replace_map[$namespace] = $package_root->append($source)->to_string();
        }
    };

    foreach ($meta->packages as $package) {
        $map_package_namespaces($package);
    }

    foreach ($config->map as $namespace => $source) {
        $replace_map[$namespace] = $project->build_root->append($source)->to_string();
    }

    return $replace_map;
}

function should_compile_files_and_directories_for_package(Project $project, Config $config, Package $package): array
{
    $package_config = $package->config($project, $config);
    $package_root = $package->root($project, $config);

    $excluded_paths = array_map(
        function ($excluded_path) use ($package, $package_root) {
            return $package_root->directory() . $excluded_path;
        },
        array_merge(['.', '..', '.git'], $package_config->excludes)
    );

    $files_and_directories = scandir($package->root($project, $config)->to_string());

    return array_filter(
        $files_and_directories,
        function ($file_or_directory) use ($package, $excluded_paths, $package_root) {
            $file_or_directory_path = $package_root->directory() . $file_or_directory;
            return ! in_array($file_or_directory_path, $excluded_paths);
        },
    );
}

function should_compile_files_and_directories(Project $project, Config $config): array
{
    $excluded_paths = array_map(
        function ($excluded_path) use ($project) {
            return $project->root->directory() . $excluded_path;
        },
        array_merge(['.', '..', 'builds', '.git', '.idea', $config->packages_directory], $config->excludes)
    );

    $files_and_directories = scandir($project->root->to_string());

    return array_filter(
        $files_and_directories,
        function ($file_or_directory) use ($project, $excluded_paths) {
            $file_or_directory_path = $project->root->directory() . $file_or_directory;
            return ! in_array($file_or_directory_path, $excluded_paths);
        },
    );
}

function file_needs_modification(Address $file, Config $config): bool
{
    return array_reduce(
            array: array_merge(array_values($config->executables), $config->entry_points),
            callback: fn ($carry, $entry_point) => str_ends_with($file->to_string(), $entry_point) || $carry,
            initial: false
        )
        || str_ends_with($file->to_string(), '.php');
}

function add_autoloads(Address $target, array $replace_map, array $autoloads): void
{
    $autoload_lines = [];

    $autoload_lines = array_merge($autoload_lines, [
        '',
        'spl_autoload_register(function ($class) {',
        '    $classes = [',
    ]);

    foreach ($autoloads as $class => $path) {
        $autoload_lines[] = "        '$class' => '$path',";
    }

    $autoload_lines = array_merge($autoload_lines, [
        '    ];',
        '',
        '    if (array_key_exists($class, $classes)) {',
        '        require $classes[$class];',
        '    }',
        '',
        '}, true, true);',
    ]);

    $autoload_lines = array_merge($autoload_lines, [
        '',
        'spl_autoload_register(function ($class) {',
        '    $namespaces = [',
    ]);

    foreach ($replace_map as $namespace => $path) {
        $autoload_lines[] = "        '$namespace' => '$path',";
    }

    $autoload_lines = array_merge($autoload_lines, [
        '    ];',
        '',
        '    $realpath = null;',
        '',
        '    foreach ($namespaces as $namespace => $path) {',
        '        if (str_starts_with($class, $namespace)) {',
        '            $pos = strpos($class, $namespace);',
        '            if ($pos !== false) {',
        '                $realpath = substr_replace($class, $path, $pos, strlen($namespace));',
        '            }',
        '            $realpath = str_replace("\\\", DIRECTORY_SEPARATOR, $realpath) . \'.php\';',
        '            require $realpath;',
        '            return ;',
        '        }',
        '    }',
        '});',
    ]);

    $lines = explode(PHP_EOL, file_get_contents($target->to_string()));
    $number = 1;
    foreach ($lines as $line_number => $line) {
        if (str_contains($line, '<?php')) {
            $number = $line_number;
            break;
        }
    }

    $lines = array_insert_after($lines, $number, $autoload_lines);
    File\modify($target->to_string(), implode(PHP_EOL, $lines));
}
