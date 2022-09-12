<?php

namespace Saeghe\Saeghe\Commands\Migrate;

function run()
{
    global $projectRoot;
    global $configPath;
    global $metaFilePath;

    $packagesDirectory = find_or_create_packages_directory();

    $config = ['map' => [], 'excludes' => ['vendor']];
    $meta = [];

    $composerSetting = json_decode(file_get_contents($projectRoot . '/composer.json'), true);
    $composerLockSetting = json_decode(file_get_contents($projectRoot . '/composer.lock'), true);

    if (isset($composerSetting['autoload']['psr-4'])) {
        $config['map'] = [];
        foreach ($composerSetting['autoload']['psr-4'] as $namespace => $path) {
            $namespace = str_ends_with($namespace, '\\') ? substr_replace($namespace, '', -1) : $namespace;
            $path = str_ends_with($path, '/') ? substr_replace($path, '', -1) : $path;

            $config['map'][$namespace] = $path;
        }
    }

    if (isset($composerLockSetting['packages'])) {
        foreach ($composerLockSetting['packages'] as $packageMeta) {
            $name = $packageMeta['name'];
            $package = $packageMeta['source']['url'];
            $version = $packageMeta['version'];
            $hash = $packageMeta['source']['reference'];
            $ownerAndRepo = get_meta_from_package($package);

            if (isset($composerSetting['require'][$name])) {
                $config['packages'][$package] = $version;
            }

            $meta['packages'][$package] = [
                'version' => $version,
                'hash' => $hash,
                'owner' => $ownerAndRepo['owner'],
                'repo' => $ownerAndRepo['repo'],
            ];

            migrate_package($packagesDirectory, $name, $package, $meta['packages'][$package]);
        }
    }

    file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT) . PHP_EOL);
    file_put_contents($metaFilePath, json_encode($meta, JSON_PRETTY_PRINT) . PHP_EOL);
}

function migrate_package($packagesDirectory, $name, $package, $packageMeta)
{
    global $projectRoot;

    $packageVendorDirectory = $projectRoot . 'vendor/' . $name;

    $packageDirectory = $packagesDirectory . $packageMeta['owner'] . '/' . $packageMeta['repo'];

    if (! file_exists($packageDirectory)) {
        mkdir($packageDirectory, 0755, true);
    }

    recursive_copy($packageVendorDirectory, $packageDirectory);

    $packageComposerSettings = json_decode(file_get_contents($packageDirectory . '/composer.json'), true);

    $config = ['map' => []];

    if (isset($packageComposerSettings['autoload']['psr-4'])) {
        foreach ($packageComposerSettings['autoload']['psr-4'] as $namespace => $path) {
            // TODO:
            if (! is_array($namespace) && ! is_array($path)) {
                $namespace = str_ends_with($namespace, '\\') ? substr_replace($namespace, '', -1) : $namespace;
                $path = str_ends_with($path, '/') ? substr_replace($path, '', -1) : $path;

                $config['map'][$namespace] = $path;
            }
        }
    }

    file_put_contents($packageDirectory . '/build.json', json_encode($config, JSON_PRETTY_PRINT) . PHP_EOL);
    file_put_contents($packageDirectory . '/build-lock.json', json_encode([], JSON_PRETTY_PRINT) . PHP_EOL);
}

function find_or_create_packages_directory()
{
    global $packagesDirectory;

    if (! file_exists($packagesDirectory)) {
        mkdir($packagesDirectory);
    }

    return $packagesDirectory;
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
