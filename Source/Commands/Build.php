<?php

namespace Saeghe\Saeghe\Commands\Build;

use Saeghe\Cli\IO\Write;
use Saeghe\FileManager\Path;
use Saeghe\Saeghe\Config\Config;
use Saeghe\FileManager\Filesystem\Directory;
use Saeghe\FileManager\Filesystem\File;
use Saeghe\FileManager\Filesystem\FilesystemCollection;
use Saeghe\FileManager\Filesystem\Symlink;
use Saeghe\Saeghe\Config\Meta;
use Saeghe\Saeghe\Map;
use Saeghe\Saeghe\Package;
use Saeghe\FileManager\FileType\Json;
use Saeghe\Saeghe\PhpFile;
use Saeghe\Saeghe\Project;
use Saeghe\Datatype\Str;

function run(Project $project)
{
    Write\line('Start building...');

    Write\line('Reading configs...');
    $config = $project->config->exists()
        ? Config::from_array(Json\to_array($project->config))
        : Config::init();

    $meta = $project->config_lock->exists()
        ? Meta::from_array(Json\to_array($project->config_lock))
        : Meta::init();

    Write\line('Checking packages...');
    $packages_installed = $meta->packages
        ->every(fn (Package $package, string $package_url) => $package->root($project, $config)->exists());

    if (! $packages_installed) {
        Write\error('It seems you didn\'t run the install command. Please make sure you installed your required packages.');
        return;
    }

    Write\line('Prepare build directory...');
    $project->build_root->renew_recursive();
    $project->build_root->subdirectory($config->packages_directory)->exists_or_create();

    Write\line('Make namespaces map...');
    make_replace_map($project, $config, $meta);

    Write\line('Building packages...');
    foreach ($meta->packages as $package_url => $package) {
        Write\line('Building package ' . $package_url . '...');
        compile_packages($project, $config, $package);
    }

    Write\line('Building the project...');
    compile_project_files($project, $config);

    Write\line('Building entry points...');
    foreach ($config->entry_points as $entry_point) {
        Write\line('Building entry point ' . $entry_point);
        add_autoloads($project, $project->build_root->file($entry_point));
    }

    Write\line('Building executables...');
    foreach ($meta->packages as $package_url => $package) {
        Write\line('Building executables for package ' . $package_url);
        $package_config = $package->config($project, $config);
        foreach ($package_config->executables as $link_name => $source) {
            Write\line('Building executable file ' . $link_name . ' from ' . $source);
            add_executables($project, $config, $package, $link_name, $source);
        }
    }

    Write\success('Build finished successfully.');
}

function add_executables(Project $project, Config $config, Package $package, string $link_name, string $source): void
{
    $target = $package->build_root($project, $config)->file($source);
    $link = $project->build_root->symlink($link_name);
    $link->link($target);
    add_autoloads($project, $target);
    $target->chmod(0774);
}

function compile_packages(Project $project, Config $config, Package $package): void
{
    $project->build_root->subdirectory("$config->packages_directory/$package->owner/$package->repo")->renew_recursive();

    package_compilable_files_and_directories($project, $config, $package)
        ->each(
            fn (Directory|File|Symlink $filesystem)
                => compile(
                    $project,
                    $package->config($project, $config),
                    $filesystem,
                    $package->root($project, $config),
                    $package->build_root($project, $config)
                )
        );
}

function compile_project_files(Project $project, Config $config): void
{
    compilable_files_and_directories($project, $config)
        ->each(fn (Directory|File|Symlink $filesystem)
            => compile($project, $config, $filesystem, $project->root, $project->build_root)
        );
}

function compile(Project $project, Config $config, Directory|File|Symlink $address, Directory $origin, Directory $destination): void
{
    $destination_path = $address->relocate($origin, $destination);

    if ($address instanceof Directory) {
        $address->preserve_copy($destination_path->as_directory());

        $address->ls_all()
            ->each(
                fn (Directory|File|Symlink $filesystem)
                => compile(
                    $project,
                    $config,
                    $filesystem,
                    $origin->subdirectory($address->leaf()),
                    $destination->subdirectory($address->leaf())
                )
            );

        return;
    }

    if ($address instanceof Symlink) {
        $source_link = $address->parent()->file(readlink($address));
        $destination_path->as_symlink()->link($source_link);

        return;
    }

    if (file_needs_modification($address, $config)) {
        compile_file($project, $address, $destination_path->as_file());

        return;
    }

    $address->preserve_copy($destination_path->as_file());
}

function compile_file(Project $project, File $origin, File $destination): void
{
    $destination->create(apply_file_modifications($project, $origin), $origin->permission());
}

function apply_file_modifications(Project $project, File $origin): string
{
    $php_file = PhpFile::from_content($origin->content());
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

    $paths = new Map([]);

     array_walk($imports, function ($import) use ($project, $paths) {
        $path = $project->namespaces->find($import, true);
        $import = $path ? $import : Str\before_last_occurrence($import, '\\');
        $path = $path ?: $project->namespaces->find($import, false);
        unless(is_null($path), fn () => $paths->put($path, $import));
    });

    array_walk($autoload, function ($import) use ($project) {
        $path = $project->namespaces->find($import, false);
        unless(is_null($path), fn () => $project->imported_classes->put($path, $import));
    });

    if ($paths->count() === 0) {
        return $php_file->code();
    }

    $require_statements = array_map(fn(Path $path) => "require_once '$path';", $paths->items());

    $php_file = $php_file->has_namespace()
        ? $php_file->add_after_namespace(PHP_EOL . PHP_EOL . implode(PHP_EOL, $require_statements))
        : $php_file->add_after_opening_tag(PHP_EOL . implode(PHP_EOL, $require_statements) . PHP_EOL);

    return $php_file->code();
}

function make_replace_map(Project $project, Config $config, Meta $meta): void
{
    $map_package_namespaces = function (Package $package) use ($project, $config) {
        $package_config = $package->config($project, $config);
        $package_root = $package->build_root($project, $config);

        foreach ($package_config->map as $namespace => $source) {
            $project->namespaces->put($package_root->append($source), $namespace);
        }
    };

    foreach ($meta->packages as $package) {
        $map_package_namespaces($package);
    }

    foreach ($config->map as $namespace => $source) {
        $project->namespaces->put($project->build_root->append($source), $namespace);
    }
}

function package_compilable_files_and_directories(Project $project, Config $config, Package $package): FilesystemCollection
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

function compilable_files_and_directories(Project $project, Config $config): FilesystemCollection
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
            ->has(fn (string $entry_point) => str_ends_with($file, $entry_point));
}

function add_autoloads(Project $project, File $target): void
{
    $content = <<<'EOD'
spl_autoload_register(function ($class) {
    $classes = [

EOD;

    foreach ($project->imported_classes as $class => $path) {
        $content .= <<<EOD
        '$class' => '$path',

EOD;
    }

    $content .= <<<'EOD'
    ];

    if (array_key_exists($class, $classes)) {
        require $classes[$class];
    }

}, true, true);

spl_autoload_register(function ($class) {
    $namespaces = [

EOD;

    foreach ($project->namespaces as $namespace => $path) {
        $content .= <<<EOD
        '$namespace' => '$path',

EOD;
    }
    $content .= <<<'EOD'
    ];

    $realpath = null;

    foreach ($namespaces as $namespace => $path) {
        if (str_starts_with($class, $namespace)) {
            $pos = strpos($class, $namespace);
            if ($pos !== false) {
                $realpath = substr_replace($class, $path, $pos, strlen($namespace));
            }
            $realpath = str_replace("\\", DIRECTORY_SEPARATOR, $realpath) . '.php';
            require $realpath;
            return ;
        }
    }
});
EOD;

    $php_file = PhpFile::from_content($target->content());
    $php_file = $php_file->add_after_opening_tag(PHP_EOL . $content . PHP_EOL);
    $target->modify($php_file->code());
}
