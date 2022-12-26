<?php

namespace Saeghe\Saeghe\Commands\Migrate;

use Saeghe\Cli\IO\Write;
use Saeghe\Datatype\Map;
use Saeghe\FileManager\Filesystem\Filename;
use Saeghe\Saeghe\Classes\Config\Config;
use Saeghe\Saeghe\Classes\Config\Library;
use Saeghe\Saeghe\Classes\Config\NamespaceFilePair;
use Saeghe\Saeghe\Classes\Config\PackageAlias;
use Saeghe\Saeghe\Classes\Meta\Dependency;
use Saeghe\Saeghe\Classes\Meta\Meta;
use Saeghe\Saeghe\Classes\Environment\Environment;
use Saeghe\FileManager\FileType\Json;
use Saeghe\Saeghe\Classes\Package\Package;
use Saeghe\Saeghe\Classes\Project\Project;
use Saeghe\Saeghe\Git\Repository;
use function Saeghe\Cli\IO\Read\parameter;
use function Saeghe\FileManager\Directory\preserve_copy_recursively;

function run(Environment $environment): void
{
    $project = new Project($environment->pwd->subdirectory(parameter('project', '')));

    $composer_file = $project->root->file('composer.json');
    $composer_lock_file = $project->root->file('composer.lock');

    if (! $composer_file->exists()) {
        Write\error('There is no composer.json file in this project!');
        return;
    }

    if (! $composer_lock_file->exists()) {
        Write\error('There is no composer.lock file in this project!');
        return;
    }

    $composer_setting = Json\to_array($composer_file);
    $composer_lock_setting = Json\to_array($composer_lock_file);

    $config = Config::init();
    $config->excludes->push(new Filename('vendor'));
    $meta = Meta::init();

    $project->config($config);
    $project->meta = $meta;

    if ($project->packages_directory->exists()) {
        Write\error('There is a Packages directory in your project.');
        return;
    }

    $project->packages_directory->make();

    migrate($project, $composer_setting, $composer_lock_setting);

    Write\success('Project migrated successfully.');
}

function migrate(Project $project, array $composer_setting, array $composer_lock_setting): void
{
    $project->config->map = psr4_to_map($composer_setting['autoload']['psr-4'] ?? []);

    $requires = array_merge(
        $composer_setting['require'] ?? [],
        $composer_setting['require-dev'] ?? [],
    );

    $installed_packages = array_merge(
        $composer_lock_setting['packages'] ?? [],
        $composer_lock_setting['packages-dev'] ?? [],
    );

    foreach ($installed_packages as $package_meta) {
        $alias = $package_meta['name'];
        $package_url = $package_meta['source']['url'];
        $repository = Repository::from_url($package_url);
        $repository->version($package_meta['version']);
        $repository->hash($package_meta['source']['reference']);
        $library = new Library($package_url, $repository);

        if (isset($requires[$alias])) {
            $project->config->repositories->push($library);
            $project->config->aliases->push(new PackageAlias($alias, $package_url));
        }

        $project->meta->dependencies->push(new Dependency($package_url, $library->meta()));
        $project->packages->put(new Package($project->package_directory($repository), $repository), $alias);
    }

    $project->packages->each(function (Package $package, string $alias) use ($project) {
        $package_vendor_directory = $project->root->subdirectory('vendor/' . $alias);
        $package->root->make_recursive();
        preserve_copy_recursively($package_vendor_directory, $package->root);

        $package_config = Config::init();
        $package_config->excludes->push(new Filename('vendor'));
        $package_composer_settings = Json\to_array($package->root->file('composer.json'));
        $package_config->map = psr4_to_map($package_composer_settings['autoload']['psr-4'] ?? []);

        Json\write($package->config_file, $package_config->to_array());
        Json\write($package->root->file( 'saeghe.config-lock.json'), []);
    });

    Json\write($project->config_file, $project->config->to_array());
    Json\write($project->meta_file, $project->meta->to_array());
}

function psr4_to_map(array $psr4): Map
{
    $map = new Map();

    foreach ($psr4 as $namespace => $path) {
        if (! is_array($namespace) && ! is_array($path)) {
            $namespace = str_ends_with($namespace, '\\') ? substr_replace($namespace, '', -1) : $namespace;
            $path = str_ends_with($path, '/') ? substr_replace($path, '', -1) : $path;

            $map->push(new NamespaceFilePair($namespace, new Filename($path)));
        }
    }

    return $map;
}
