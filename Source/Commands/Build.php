<?php

namespace Saeghe\Saeghe\Commands\Build;

use Saeghe\Cli\IO\Write;
use Saeghe\Saeghe\Config\Config;
use Saeghe\FileManager\Filesystem\Directory;
use Saeghe\FileManager\Filesystem\File;
use Saeghe\FileManager\Filesystem\FilesystemCollection;
use Saeghe\FileManager\Filesystem\Symlink;
use Saeghe\Saeghe\Config\Meta;
use Saeghe\Saeghe\Package;
use Saeghe\FileManager\FileType\Json;
use Saeghe\Saeghe\PhpFile;
use Saeghe\Saeghe\Project;
use Saeghe\Datatype\Arr;
use Saeghe\Datatype\Str;
use function Saeghe\FileManager\Resolver\realpath;

$autoloads = [];

function run(Project $project)
{
    $config = $project->config->exists()
        ? Config::from_array(Json\to_array($project->config))
        : Config::init();

    $meta = $project->config_lock->exists()
        ? Meta::from_array(Json\to_array($project->config_lock))
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

    should_compile_files_and_directories_for_package($project, $config, $package)
        ->each(fn (Directory|File|Symlink $filesystem)
            => compile($package->config($project, $config), $filesystem, $package->root($project, $config), $package->build_root($project, $config), $replace_map)
        );
}

function compile_project_files(Project $project, Config $config, array $replace_map): void
{
    should_compile_files_and_directories($project, $config)
        ->each(fn (Directory|File|Symlink $filesystem)
            => compile( $config, $filesystem, $project->root, $project->build_root, $replace_map)
        );
}

function compile(Config $config, Directory|File|Symlink $address, Directory $origin, Directory $destination, array $replace_map): void
{
    $destination_path = $address->relocate($origin, $destination);

    if ($address instanceof Directory) {
        $address->preserve_copy($destination_path->as_directory());

        $address->ls_all()->each(fn (Directory|File|Symlink $filesystem)
            => compile($config, $filesystem, $origin->subdirectory($address->leaf()), $destination->subdirectory($address->leaf()), $replace_map)
        );

        return;
    }

    if ($address instanceof Symlink) {
        $source_link = $address->parent()->file(readlink($address));
        $destination_path->as_symlink()->link($source_link);

        return;
    }

    if (file_needs_modification($address, $config)) {
        compile_file($address, $destination_path->as_file(), $replace_map);

        return;
    }

    $address->preserve_copy($destination_path->as_file());
}

function compile_file(File $origin, File $destination, array $replace_map): void
{
    $destination->create(apply_file_modifications($origin, $replace_map), $origin->permission());
}

function apply_file_modifications(File $origin, array $replace_map): string
{
    global $autoloads;

    $content = $origin->content();

    $php_file = PhpFile::from_content($content);
    $file_imports = $php_file->imports();

    $autoload = $file_imports['classes'];

    foreach ($autoload as $import => $alias) {
        $used_functions = $php_file->used_functions($alias);
        $used_constants = $php_file->used_constants($alias);

        if (count($used_functions) > 0 || count($used_constants) > 0) {
            foreach ($used_constants as $constant) {
                $file_imports['constants'][$import . '\\' . $constant] = $constant;
            }
            foreach ($used_functions as $function) {
                $file_imports['functions'][$import . '\\' . $function] = $function;
            }

            unset($autoload[$import]);
        }
    }

    $imports = array_keys(array_merge($file_imports['constants'], $file_imports['functions']));
    $autoload = array_keys($autoload);

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
            $replace_map[$namespace] = $package_root->append($source);
        }
    };

    foreach ($meta->packages as $package) {
        $map_package_namespaces($package);
    }

    foreach ($config->map as $namespace => $source) {
        $replace_map[$namespace] = $project->build_root->append($source);
    }

    return $replace_map;
}

function should_compile_files_and_directories_for_package(Project $project, Config $config, Package $package): FilesystemCollection
{
    $package_config = $package->config($project, $config);
    $package_root = $package->root($project, $config);

    $excluded_paths = array_map(
        function ($excluded_path) use ($package, $package_root) {
            return $package_root->append($excluded_path)->string();
        },
        $package_config->excludes->put('.git')->items()
    );

    return $package->root($project, $config)->ls_all()
        ->except(fn (Directory|File|Symlink $file_or_directory)
            => in_array($file_or_directory->path->string(), $excluded_paths)
        );
}

function should_compile_files_and_directories(Project $project, Config $config): FilesystemCollection
{
    $excluded_paths = array_map(
        function ($excluded_path) use ($project) {
            return $project->root->append($excluded_path)->string();
        },
        $config->excludes->append(['builds', '.git', '.idea', $config->packages_directory->string()])->items()
    );

    return $project->root
        ->ls_all()
        ->except(fn (Directory|File|Symlink $filesystem)
            => in_array($filesystem->path->string(), $excluded_paths)
        );
}

function file_needs_modification(File $file, Config $config): bool
{
    return str_ends_with($file, '.php')
        || $config->entry_points
            ->append($config->executables->values())
            ->reduce(fn ($carry, $entry_point)
                => str_ends_with($file, $entry_point) || $carry, false
            );
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
