<?php

namespace Saeghe\Saeghe\Commands\Add;

use Exception;

function run()
{
    $project = getopt('', ['project::'])['project'] ?? '';
    $package['path'] = getopt('', ['path::'])['path'] ?? throw new Exception('Package is required to add a path!');

    $projectDirectory = $_SERVER['PWD'] . '/' . $project;

    $packagesDirectory = findOrCreatePackagesDirectory($projectDirectory);
    $package = clonePackage($packagesDirectory, $package);
    $package['namespace'] = namespaceFromName($package['name']);
    findOrCreateBuildJsonFile($projectDirectory, $package);
    findOrCreateBuildLockFile($projectDirectory, $package);
}

function namespaceFromName($packageName)
{
    [$owner, $repo] = explode('/', $packageName);

    $repo = str_replace('-', '', ucwords($repo, '-'));

    return ucfirst($owner) . '\\' . ucfirst($repo);
}

function detectName($package)
{
    $name = str_replace('git@github.com:', '', $package);

    return substr_replace($name, '', -4);
}

function findOrCreateBuildJsonFile($projectDirectory, $package)
{
    $configFile = $projectDirectory . '/build.json';

    if (! file_exists($configFile)) {
        file_put_contents($configFile, json_encode([], JSON_PRETTY_PRINT));
    }

    $config = json_decode(file_get_contents($configFile), true, JSON_THROW_ON_ERROR);

    $config['packages'][$package['namespace']] = [
        'path' => $package['path'],
        'version' => $package['version'],
    ];

    file_put_contents($projectDirectory . '/build.json', json_encode($config, JSON_PRETTY_PRINT) . PHP_EOL);
}

function findOrCreateBuildLockFile($projectDirectory, $package)
{
    $configFile = $projectDirectory . '/build.lock';

    if (! file_exists($configFile)) {
        file_put_contents($configFile, json_encode([], JSON_PRETTY_PRINT));
    }

    $lockContent = json_decode(file_get_contents($configFile), true, JSON_THROW_ON_ERROR);

    $lockContent[$package['namespace']] = [
        'path' => $package['path'],
        'version' => $package['version'],
        'hash' => trim($package['hash']),
        'owner' => trim($package['owner']),
        'repo' => trim($package['repo']),
    ];
    file_put_contents($projectDirectory . '/build.lock', json_encode($lockContent, JSON_PRETTY_PRINT) . PHP_EOL);
}

function findOrCreatePackagesDirectory($projectDirectory)
{
    $packagesDirectory = $projectDirectory . '/Packages';

    if (! file_exists($packagesDirectory)) {
        mkdir($packagesDirectory);
    }

    return $packagesDirectory;
}

function clonePackage($packageDirectory, $package)
{
    $package['name'] = detectName($package['path']);
    [$package['owner'], $package['repo']] = explode('/', $package['name']);
    $package['destination'] = "$packageDirectory/{$package['owner']}/{$package['repo']}";
    shell_exec("git clone {$package['path']} {$package['destination']}");
    $package['hash'] = shell_exec("git --git-dir={$package['destination']}/.git rev-parse HEAD");
    $package['version'] = 'development';

    return $package;
}
