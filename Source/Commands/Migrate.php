<?php

namespace Saeghe\Saeghe\Commands\Migrate;

use Saeghe\Cli\IO\Write;
use Saeghe\Saeghe\Config;
use Saeghe\Saeghe\Meta;
use Saeghe\Saeghe\Project;

function run(Project $project)
{
    $config = Config::init()->toArray();
    $config['excludes'] = ['vendor'];
    $meta = Meta::init()->toArray();

    $composerFile = $project->root . 'composer.json';
    $composerLockFile = $project->root . 'composer.lock';

    if (! file_exists($composerFile)) {
        Write\error('There is no composer.json file in this project!');
        return;
    }

    if (! file_exists($composerLockFile)) {
        Write\error('There is no composer.lock file in this project!');
        return;
    }

    $composerSetting = json_to_array($composerFile);
    $composerLockSetting = json_to_array($composerLockFile);

    if (isset($composerSetting['autoload']['psr-4'])) {
        $config['map'] = [];
        foreach ($composerSetting['autoload']['psr-4'] as $namespace => $path) {
            $namespace = str_ends_with($namespace, '\\') ? substr_replace($namespace, '', -1) : $namespace;
            $path = str_ends_with($path, '/') ? substr_replace($path, '', -1) : $path;

            $config['map'][$namespace] = $path;
        }
    }

    $requires = array_merge(
        $composerSetting['require'] ?? [],
        $composerSetting['require-dev'] ?? [],
    );

    $installedPackages = array_merge(
        $composerLockSetting['packages'] ?? [],
        $composerLockSetting['packages-dev'] ?? [],
    );

    foreach ($installedPackages as $packageMeta) {
        $name = $packageMeta['name'];
        $package = $packageMeta['source']['url'];
        $version = $packageMeta['version'];
        $hash = $packageMeta['source']['reference'];
        $ownerAndRepo = get_meta_from_package($package);

        if (isset($requires[$name])) {
            $config['packages'][$package] = $version;
        }

        $meta['packages'][$package] = [
            'version' => $version,
            'hash' => $hash,
            'owner' => $ownerAndRepo['owner'],
            'repo' => $ownerAndRepo['repo'],
        ];

        migrate_package($project, $project->root . $config['packages-directory'] . '/', $name, $package, $meta['packages'][$package]);
    }

    json_put($project->configFilePath, $config);
    json_put($project->configLockFilePath, $meta);

    Write\success('Project migrated successfully.');
}

function migrate_package(Project $project, $packagesDirectory, $name, $package, $packageMeta)
{
    $packageVendorDirectory = $project->root . 'vendor/' . $name;

    $packageDirectory = $packagesDirectory . $packageMeta['owner'] . '/' . $packageMeta['repo'];

    if (! file_exists($packageDirectory)) {
        mkdir($packageDirectory, 0755, true);
    }

    recursive_copy($packageVendorDirectory, $packageDirectory);

    $packageComposerSettings = json_decode(file_get_contents($packageDirectory . '/composer.json'), true);

    $config = ['map' => []];

    if (isset($packageComposerSettings['autoload']['psr-4'])) {
        foreach ($packageComposerSettings['autoload']['psr-4'] as $namespace => $path) {
            if (! is_array($namespace) && ! is_array($path)) {
                $namespace = str_ends_with($namespace, '\\') ? substr_replace($namespace, '', -1) : $namespace;
                $path = str_ends_with($path, '/') ? substr_replace($path, '', -1) : $path;

                $config['map'][$namespace] = $path;
            }
        }
    }

    file_put_contents($packageDirectory . '/saeghe.config.json', json_encode($config, JSON_PRETTY_PRINT) . PHP_EOL);
    file_put_contents($packageDirectory . '/saeghe.config-lock.json', json_encode([], JSON_PRETTY_PRINT) . PHP_EOL);
}

function get_meta_from_package($package)
{
    if (str_starts_with($package, 'https:')) {
        $ownerAndRepo = str_replace('https://github.com/', '', $package);
    } else {
        $ownerAndRepo = str_replace('git@github.com:', '', $package);
    }

    if (str_ends_with($ownerAndRepo, '.git')) {
        $ownerAndRepo = substr_replace($ownerAndRepo, '', -4);
    }

    [$meta['owner'], $meta['repo']] = explode('/', $ownerAndRepo);

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

        $nextSource = $source . '/' . $file;
        $nextDestination = $destination . '/' . $file;

        if (is_dir($nextSource)) {
            recursive_copy($nextSource, $nextDestination);
        } else {
            copy($nextSource, $nextDestination);
        }
    }

    closedir($dir);
}
