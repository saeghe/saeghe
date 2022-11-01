<?php

namespace Saeghe\Saeghe\Commands\Migrate;

use Saeghe\Cli\IO\Write;
use Saeghe\Saeghe\Config;
use Saeghe\Saeghe\Meta;
use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\Project;

function run(Project $project)
{
    $config = Config::init()->to_array();
    $config['excludes'] = ['vendor'];
    $meta = Meta::init()->to_array();

    $composer_file = $project->root->directory() . 'composer.json';
    $composer_lock_file = $project->root->directory() . 'composer.lock';

    if (! file_exists($composer_file)) {
        Write\error('There is no composer.json file in this project!');
        return;
    }

    if (! file_exists($composer_lock_file)) {
        Write\error('There is no composer.lock file in this project!');
        return;
    }

    $composer_setting = json_to_array($composer_file);
    $composer_lock_setting = json_to_array($composer_lock_file);

    if (isset($composer_setting['autoload']['psr-4'])) {
        $config['map'] = [];
        foreach ($composer_setting['autoload']['psr-4'] as $namespace => $path) {
            $namespace = str_ends_with($namespace, '\\') ? substr_replace($namespace, '', -1) : $namespace;
            $path = str_ends_with($path, '/') ? substr_replace($path, '', -1) : $path;

            $config['map'][$namespace] = $path;
        }
    }

    $requires = array_merge(
        $composer_setting['require'] ?? [],
        $composer_setting['require-dev'] ?? [],
    );

    $installed_packages = array_merge(
        $composer_lock_setting['packages'] ?? [],
        $composer_lock_setting['packages-dev'] ?? [],
    );

    foreach ($installed_packages as $package_meta) {
        $name = $package_meta['name'];
        $package = $package_meta['source']['url'];
        $version = $package_meta['version'];
        $hash = $package_meta['source']['reference'];
        $owner_and_repo = get_meta_from_package($package);

        if (isset($requires[$name])) {
            $config['packages'][$package] = $version;
        }

        $meta['packages'][$package] = [
            'version' => $version,
            'hash' => $hash,
            'owner' => $owner_and_repo['owner'],
            'repo' => $owner_and_repo['repo'],
        ];

        migrate_package($project, $project->root->append($config['packages-directory']), $name, $package, $meta['packages'][$package]);
    }

    json_put($project->config_file_path->to_string(), $config);
    json_put($project->config_lock_file_path->to_string(), $meta);

    Write\success('Project migrated successfully.');
}

function migrate_package(Project $project, Address $packages_directory, $name, $package, $package_meta)
{
    $package_vendor_directory = $project->root->append('vendor/' . $name)->to_string();

    $package_directory = $packages_directory->append($package_meta['owner'] . '/' . $package_meta['repo']);

    if (! file_exists($package_directory->to_string())) {
        mkdir($package_directory->to_string(), 0755, true);
    }

    recursive_copy($package_vendor_directory, $package_directory->to_string());

    $package_composer_settings = json_decode(file_get_contents($package_directory->append('composer.json')->to_string()), true);

    $config = ['map' => []];

    if (isset($package_composer_settings['autoload']['psr-4'])) {
        foreach ($package_composer_settings['autoload']['psr-4'] as $namespace => $path) {
            if (! is_array($namespace) && ! is_array($path)) {
                $namespace = str_ends_with($namespace, '\\') ? substr_replace($namespace, '', -1) : $namespace;
                $path = str_ends_with($path, '/') ? substr_replace($path, '', -1) : $path;

                $config['map'][$namespace] = $path;
            }
        }
    }

    file_put_contents($package_directory->append('saeghe.config.json')->to_string(), json_encode($config, JSON_PRETTY_PRINT) . PHP_EOL);
    file_put_contents($package_directory->append( 'saeghe.config-lock.json')->to_string(), json_encode([], JSON_PRETTY_PRINT) . PHP_EOL);
}

function get_meta_from_package($package)
{
    if (str_starts_with($package, 'https:')) {
        $owner_and_repo = str_replace('https://github.com/', '', $package);
    } else {
        $owner_and_repo = str_replace('git@github.com:', '', $package);
    }

    if (str_ends_with($owner_and_repo, '.git')) {
        $owner_and_repo = substr_replace($owner_and_repo, '', -4);
    }

    [$meta['owner'], $meta['repo']] = explode('/', $owner_and_repo);

    return $meta;
}

function recursive_copy($source, $destination)
{
    $dir = opendir($source);
    @mkdir($destination, 0755, true);

    while (($file = readdir($dir)) ) {
        if (in_array($file, ['.', '..' ])) {
            continue;
        }

        $next_source = $source . DIRECTORY_SEPARATOR . $file;
        $next_destination = $destination . DIRECTORY_SEPARATOR . $file;

        if (is_dir($next_source)) {
            recursive_copy($next_source, $next_destination);
        } else {
            copy($next_source, $next_destination);
        }
    }

    closedir($dir);
}
