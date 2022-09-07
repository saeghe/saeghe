<?php

namespace Saeghe\Saeghe\Commands\Add;

use function Saeghe\Cli\IO\Read\argument;

function run()
{
    $package = argument('package');
    $version = null;

    $packagesDirectory = findOrCreatePackagesDirectory();

    add($packagesDirectory, $package, $version);
}

function add($packagesDirectory, $package, $version = null, $submodule = false)
{
    global $projectRoot;

    $meta = clonePackage($packagesDirectory, $package, $version);

    if (! $submodule) {
        findOrCreateBuildJsonFile($projectRoot, $package, $meta);
    }

    findOrCreateBuildLockFile($projectRoot, $package, $meta);

    $packagePath = $packagesDirectory . '/' . $meta['owner'] . '/' . $meta['repo'] . '/';
    $packageConfig = $packagePath . 'build.json';
    if (file_exists($packageConfig)) {
        $packageSetting = json_decode(json: file_get_contents($packageConfig), associative: true, flags: JSON_THROW_ON_ERROR);
        foreach ($packageSetting['packages'] as $subPackage => $version) {
            add($packagesDirectory, $subPackage, $version, true);
        }
    }
}

function findOrCreateBuildJsonFile($projectDirectory, $package, $meta)
{
    $configFile = $projectDirectory . 'build.json';

    if (! file_exists($configFile)) {
        file_put_contents($configFile, json_encode([], JSON_PRETTY_PRINT));
    }

    $config = json_decode(file_get_contents($configFile), true, JSON_THROW_ON_ERROR);

    $config['packages'][$package] = $meta['version'];

    file_put_contents($projectDirectory . 'build.json', json_encode($config, JSON_PRETTY_PRINT) . PHP_EOL);
}

function findOrCreateBuildLockFile($projectDirectory, $package, $meta)
{
    $configFile = $projectDirectory . 'build-lock.json';

    if (! file_exists($configFile)) {
        file_put_contents($configFile, json_encode([], JSON_PRETTY_PRINT));
    }

    $lockContent = json_decode(file_get_contents($configFile), true, JSON_THROW_ON_ERROR);

    $lockContent['packages'][$package] = [
        'version' => $meta['version'],
        'hash' => trim($meta['hash']),
        'owner' => trim($meta['owner']),
        'repo' => trim($meta['repo']),
    ];
    file_put_contents($projectDirectory . 'build-lock.json', json_encode($lockContent, JSON_PRETTY_PRINT) . PHP_EOL);
}

function findOrCreatePackagesDirectory()
{
    global $packagesDirectory;

    if (! file_exists($packagesDirectory)) {
        mkdir($packagesDirectory);
    }

    return $packagesDirectory . '/';
}

function clonePackage($packageDirectory, $package, $version)
{
    $ownerAndRepo = str_replace('git@github.com:', '', $package);
    if (str_ends_with($ownerAndRepo, '.git')) {
        $ownerAndRepo = substr_replace($ownerAndRepo, '', -4);
    }

    [$meta['owner'], $meta['repo']] = explode('/', $ownerAndRepo);

    $destination = "$packageDirectory{$meta['owner']}/{$meta['repo']}";

    shell_exec("git clone $package $destination");

    $meta['hash'] = shell_exec("git --git-dir=$destination/.git rev-parse HEAD");
    $meta['version'] = 'development';

    return $meta;
}
