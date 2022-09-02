<?php

namespace Saeghe\Saeghe\Commands\Add;

use Exception;

function run()
{
    $project = getopt('', ['project::'])['project'] ?? '';
    $path = getopt('', ['path::'])['path'] ?? throw new Exception('Package is required to add a path!');

    $projectDirectory = $_SERVER['PWD'] . '/' . $project;
    $config = findOrCreateBuildJsonFile($projectDirectory);
    $packagesDirectory = findOrCreatePackagesDirectory($projectDirectory);
    $packageName = detectName($path);
    $version = clonePackage($packagesDirectory, $packageName, $path);
    $namespace = namespaceFromName($packageName);

    $config['packages'][$namespace] = [
        'path' => $path,
        'version' => $version,
    ];

    saveBuildJson($projectDirectory, $config);
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
    shell_exec("git clone $path $packageDirectory/$owner/$repo");

    return 'dev-master';
}

function saveBuildJson($projectDirectory, $config)
{
    file_put_contents($projectDirectory . '/build.json', json_encode($config, JSON_PRETTY_PRINT) . PHP_EOL);
}
