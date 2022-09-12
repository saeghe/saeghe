<?php

namespace Saeghe\Saeghe\Commands\Remove;

use function Saeghe\Cli\IO\Read\argument;

function run()
{
    $package = argument('package');

    global $config;
    global $meta;
    global $packagesDirectory;

    remove($package, $config, $meta, $packagesDirectory);
}

function remove($package, $config, $meta, $packagesDirectory)
{
    global $projectRoot;

    $packageMeta = $meta['packages'][$package];

    $packageConfigPath = $packagesDirectory . $packageMeta['owner'] . '/' . $packageMeta['repo'] . '/' . DEFAULT_CONFIG_FILENAME;

    $packageConfig = file_exists($packageConfigPath) ? json_decode(file_get_contents($packageConfigPath), true) : [];

    $packageConfig['packages'] = $packageConfig['packages'] ?? [];

    remove_package_from_meta($package);

    remove_package_from_config($package);

    delete_package_from_packages($package);

    foreach ($packageConfig['packages'] as $subpackage => $version) {
        if (! isset($config['packages'][$subpackage])) {
            remove($subpackage, $config, $meta, $packagesDirectory);
        }
    }

    if (isset($packageConfig['executables'])) {
        foreach ($packageConfig['executables'] as $link => $path) {
            shell_exec('rm -f ' . $projectRoot . $link);
        }
    }
}

function remove_package_from_meta($package)
{
    global $metaFilePath;

    $meta = json_decode(json: file_get_contents($metaFilePath), associative: true, flags: JSON_THROW_ON_ERROR);
    unset($meta['packages'][$package]);

    file_put_contents($metaFilePath, json_encode($meta, JSON_PRETTY_PRINT));
}

function remove_package_from_config($package)
{
    global $configPath;

    $config = json_decode(json: file_get_contents($configPath), associative: true, flags: JSON_THROW_ON_ERROR);
    unset($config['packages'][$package]);

    file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT));
}

function delete_package_from_packages($package)
{
    global $packagesDirectory;
    global $meta;

    $packageMeta = $meta['packages'][$package];

    shell_exec('rm -fR ' . $packagesDirectory . $packageMeta['owner'] . '/' . $packageMeta['repo']);
}
