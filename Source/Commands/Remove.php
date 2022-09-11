<?php

namespace Saeghe\Saeghe\Commands\Remove;

use function Saeghe\Cli\IO\Read\argument;

function run()
{
    $package = argument('package');

    global $setting;
    global $lockSetting;
    global $packagesDirectory;

    remove($package, $setting, $lockSetting, $packagesDirectory);
}

function remove($package, $setting, $lockSetting, $packagesDirectory)
{
    global $projectRoot;

    $meta = $lockSetting['packages'][$package];

    $buildFile = $packagesDirectory . $meta['owner'] . '/' . $meta['repo'] . '/build.json';

    $packageSettings = file_exists($buildFile) ? json_decode(file_get_contents($buildFile), true) : [];

    $packageSettings['packages'] = $packageSettings['packages'] ?? [];

    remove_package_from_lock($package);

    remove_package_from_build($package);

    delete_package_from_packages($package);

    foreach ($packageSettings['packages'] as $subpackage => $version) {
        if (! isset($setting['packages'][$subpackage])) {
            remove($subpackage, $setting, $lockSetting, $packagesDirectory);
        }
    }

    if (isset($packageSettings['executables'])) {
        foreach ($packageSettings['executables'] as $link => $path) {
            shell_exec('rm -f ' . $projectRoot . $link);
        }
    }
}

function remove_package_from_lock($package)
{
    global $lockPath;

    $lockSetting = json_decode(json: file_get_contents($lockPath), associative: true, flags: JSON_THROW_ON_ERROR);
    unset($lockSetting['packages'][$package]);

    file_put_contents($lockPath, json_encode($lockSetting, JSON_PRETTY_PRINT));
}

function remove_package_from_build($package)
{
    global $configPath;

    $setting = json_decode(json: file_get_contents($configPath), associative: true, flags: JSON_THROW_ON_ERROR);
    unset($setting['packages'][$package]);

    file_put_contents($configPath, json_encode($setting, JSON_PRETTY_PRINT));
}

function delete_package_from_packages($package)
{
    global $packagesDirectory;
    global $lockSetting;

    $meta = $lockSetting['packages'][$package];

    shell_exec('rm -fR ' . $packagesDirectory . $meta['owner'] . '/' . $meta['repo']);
}
