<?php

namespace Saeghe\Saeghe\Commands\Build;

use Saeghe\Cli\IO\Write;
use Saeghe\Saeghe\Config;
use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use Saeghe\Saeghe\FileManager\Filesystem\File;
use Saeghe\Saeghe\FileManager\Filesystem\Symlink;
use Saeghe\Saeghe\Meta;
use Saeghe\Saeghe\Package;
use Saeghe\Saeghe\FileManager\FileType\Json;
use Saeghe\Saeghe\PhpFile;
use Saeghe\Saeghe\Project;
use Saeghe\Saeghe\DataType\Arr;
use Saeghe\Saeghe\DataType\Str;
use function Saeghe\Saeghe\FileManager\Resolver\realpath;

$autoloads = [];

function run(Project $project)
{
    $config = $project->config->exists()
        ? Config::from_array(Json\to_array($project->config->stringify()))
        : Config::init();

    $meta = $project->config_lock->exists()
        ? Meta::from_array(Json\to_array($project->config_lock->stringify()))
        : Meta::init();

    $project->build_root->renew_recursive();
    $project->build_root->subdirectory($config->packages_directory)->exists_or_create();

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

    Write\success('Build finished successfully.');
}

function make_entry_points(Project $project, Config $config, array $replace_map, array $autoloads): void
{
    foreach ($config->entry_points as $entry_point) {
        add_autoloads($project->build_root->file($entry_point), $replace_map, $autoloads);
    }
}

function add_executables(Project $project, Config $config, Package $package, array $replace_map, array $autoloads): void
{
    $package_config = $package->config($project, $config);
    foreach ($package_config->executables as $link_name => $source) {
        $target = $package->build_root($project, $config)->file($source);
        $link = $project->build_root->symlink($link_name);
        $link->link($target);
        add_autoloads($target, $replace_map, $autoloads);
        $target->chmod(0774);
    }
}

function compile_packages(Project $project, Config $config, Package $package, array $replace_map): void
{
    $project->build_root->subdirectory("{$config->packages_directory}/{$package->owner}/{$package->repo}")->renew_recursive();

    $files_and_directories = should_compile_files_and_directories_for_package($project, $config, $package);

    foreach ($files_and_directories as $file_or_directory) {
        compile(
            $package->config($project, $config),
            $file_or_directory,
            $package->root($project, $config),
            $package->build_root($project, $config),
            $replace_map
        );
    }
}

function compile_project_files(Project $project, Config $config, array $replace_map): void
{
    $files_and_directories = should_compile_files_and_directories($project, $config);

    foreach ($files_and_directories as $file_or_directory) {
        compile(
            $config,
            $file_or_directory,
            $project->root,
            $project->build_root,
            $replace_map
        );
    }
}

function compile(Config $config, Directory|File|Symlink $address, Directory $origin, Directory $destination, array $replace_map): void
{
    $destination_address = Str\replace_first_occurrence($address->stringify(), $origin->stringify(), $destination->stringify());

    if ($address instanceof Directory) {
        $destination_directory = new Directory($destination_address);
        $address->preserve_copy($destination_directory);

        $sub_files_and_directories = $address->ls_all();

        foreach ($sub_files_and_directories as $sub_file_or_directory) {
            compile(
                $config,
                $sub_file_or_directory,
                $origin->subdirectory($address->leaf()),
                $destination->subdirectory($address->leaf()),
                $replace_map
            );
        }

        return;
    }

    if ($address instanceof Symlink) {
        $source_link = $address->parent()->file(readlink($address->stringify()));
        (new Symlink($destination_address))->link($source_link);

        return;
    }

    if (file_needs_modification($address, $config)) {
        compile_file(
            new File($address->stringify()),
            new File($destination_address),
            $replace_map
        );

        return;
    }

    $address->preserve_copy($destination->file($address->leaf()));
}

function compile_file(File $origin, File $destination, array $replace_map): void
{
    $modified_file_content = apply_file_modifications($origin, $replace_map);
    $destination->create($modified_file_content, $origin->permission());
}

function apply_file_modifications(File $origin, array $replace_map): string
{
    global $autoloads;

    $content = $origin->content();
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

    return $realpath ? realpath($realpath) : null;
}

function add_requires_and_autoload(array $require_statements, File $file): string
{
    $content = '';

    $requires_added = false;

    foreach ($file->lines() as $line) {
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
        foreach ($file->lines() as $line) {
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
            $replace_map[$namespace] = $package_root->append($source)->stringify();
        }
    };

    foreach ($meta->packages as $package) {
        $map_package_namespaces($package);
    }

    foreach ($config->map as $namespace => $source) {
        $replace_map[$namespace] = $project->build_root->append($source)->stringify();
    }

    return $replace_map;
}

function should_compile_files_and_directories_for_package(Project $project, Config $config, Package $package): array
{
    $package_config = $package->config($project, $config);
    $package_root = $package->root($project, $config);

    $excluded_paths = array_map(
        function ($excluded_path) use ($package, $package_root) {
            return $package_root->append($excluded_path)->stringify();
        },
        array_merge(['.git'], $package_config->excludes)
    );

    return array_filter(
        $package->root($project, $config)->ls_all(),
        function (Directory|File|Symlink $file_or_directory) use ($package, $excluded_paths, $package_root) {
            return ! in_array($file_or_directory->stringify(), $excluded_paths);
        },
    );
}

function should_compile_files_and_directories(Project $project, Config $config): array
{
    $excluded_paths = array_map(
        function ($excluded_path) use ($project) {
            return $project->root->append($excluded_path)->stringify();
        },
        array_merge(['builds', '.git', '.idea', $config->packages_directory], $config->excludes)
    );

    return array_filter(
        $project->root->ls_all(),
        function (Directory|File|Symlink $file_or_directory) use ($project, $excluded_paths) {
            return ! in_array($file_or_directory->stringify(), $excluded_paths);
        },
    );
}

function file_needs_modification(File $file, Config $config): bool
{
    return array_reduce(
            array: array_merge(array_values($config->executables), $config->entry_points),
            callback: fn ($carry, $entry_point) => str_ends_with($file->stringify(), $entry_point) || $carry,
            initial: false
        )
        || str_ends_with($file->stringify(), '.php');
}

function add_autoloads(File $target, array $replace_map, array $autoloads): void
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

    $lines = explode(PHP_EOL, $target->content());
    $number = 1;
    foreach ($lines as $line_number => $line) {
        if (str_contains($line, '<?php')) {
            $number = $line_number;
            break;
        }
    }

    $lines = Arr\insert_after($lines, $number, $autoload_lines);
    $target->modify(implode(PHP_EOL, $lines));
}
