<?php

namespace Saeghe\Saeghe\Commands\Add;

use Exception;

function run()
{
    $project = getopt('', ['project::'])['project'] ?? '';
    $path = getopt('', ['path::'])['path'] ?? throw new Exception('Package is required to add a path!');

    $projectDirectory = $_SERVER['PWD'] . '/' . $project;
    $packagesDirectory = findOrCreatePackagesDirectory($projectDirectory);
    $packageName = detectName($path);
    [$version, $hash] = clonePackage($packagesDirectory, $packageName, $path);
    $namespace = namespaceFromName($packageName);

    $config = findOrCreateBuildJsonFile($projectDirectory);
    $config['packages'][$namespace] = [
        'path' => $path,
        'version' => $version,
    ];

    $lockContent = findOrCreateBuildLockFile($projectDirectory);
    $lockContent[$namespace] = [
        'path' => $path,
        'version' => $version,
        'hash' => $hash,
    ];

    saveBuildJson($projectDirectory, $config);
    saveBuildLock($projectDirectory, $lockContent);
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

function findOrCreateBuildJsonFile($path)
{
    $configFile = $path . '/build.json';

    if (! file_exists($configFile)) {
        file_put_contents($configFile, json_encode([], JSON_PRETTY_PRINT));
    }

    return json_decode(file_get_contents($configFile), true, JSON_THROW_ON_ERROR);
}

function findOrCreateBuildLockFile($path)
{
    $configFile = $path . '/build.lock';

    if (! file_exists($configFile)) {
        file_put_contents($configFile, json_encode([], JSON_PRETTY_PRINT));
    }

    return json_decode(file_get_contents($configFile), true, JSON_THROW_ON_ERROR);
}

function findOrCreatePackagesDirectory($projectDirectory)
{
    $packagesDirectory = $projectDirectory . '/Packages';

    if (! file_exists($packagesDirectory)) {
        mkdir($packagesDirectory);
    }

    return $packagesDirectory;
}

function clonePackage($packageDirectory, $name, $path)
{
    [$owner, $repo] = explode('/', $name);
    $destination = "$packageDirectory/$owner/$repo";
    shell_exec("git clone $path $destination");
    $hash = shell_exec("git --git-dir=$destination/.git rev-parse HEAD");

    return ['dev-master', trim($hash)];
}

function saveBuildJson($projectDirectory, $config)
{
    file_put_contents($projectDirectory . '/build.json', json_encode($config, JSON_PRETTY_PRINT) . PHP_EOL);
}

function saveBuildLock($projectDirectory, $lockContent)
{
    file_put_contents($projectDirectory . '/build.lock', json_encode($lockContent, JSON_PRETTY_PRINT) . PHP_EOL);
}
