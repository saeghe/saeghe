<?php

namespace Saeghe\Saeghe\Commands\Build;

use Generator;

function run()
{
    $project = getopt('', ['project::'])['project'] ?? '';
    $environment = getopt('', ['environment::'])['environment'] ?? 'development';

    $buildDirectory = resetEnvironmentBuildDirectory($project, $environment);
    $filesAndDirectories = findProjectFilesAndDirectoriesForBuild($project);

    $projectPath = $_SERVER['PWD'] . '/' . $project;

    foreach ($filesAndDirectories as $fileOrDirectory) {
        build($fileOrDirectory, $projectPath, $buildDirectory);
    }

    addExecutables();
}

function addExecutables()
{
    global $projectRoot;
    global $lockSetting;

    foreach ($lockSetting as $namespace => $package) {
        $packagePath = 'Packages/' . $package['owner'] . '/' . $package['repo'] . '/';
        $packageConfig = $projectRoot . $packagePath . 'build.json';
        $packageSetting = json_decode(json: file_get_contents($packageConfig), associative: true, flags: JSON_THROW_ON_ERROR);

        if (isset($packageSetting['executables'])) {
            foreach ($packageSetting['executables'] as $linkName => $source) {
                $target = $projectRoot . 'builds/development/' . $packagePath . $source;
                $link = $projectRoot . 'builds/development/' . $linkName;
                symlink($target, $link);
            }
        }
    }
}

function resetEnvironmentBuildDirectory($project, $environment)
{
    $buildDirectory = $_SERVER['PWD'] . '/' . $project . '/builds/' . $environment;

    shell_exec('rm -fR ' . $buildDirectory);

    mkdir($buildDirectory, 0777, true);

    return $buildDirectory;
}

function findProjectFilesAndDirectoriesForBuild($project)
{
    $projectPath = $_SERVER['PWD'] . '/' . $project;

    $excludedPaths = array_map(
        function ($excludedPath) use ($projectPath) {
            return $projectPath . '/' . $excludedPath;
        },
        ['.', '..', 'builds', '.git']
    );

    $filesAndDirectories = scandir($projectPath);

    return array_filter(
        $filesAndDirectories,
        function ($fileOrDirectory) use ($projectPath, $excludedPaths) {
            $fileOrDirectoryPath = $projectPath . '/' . $fileOrDirectory;
            return ! in_array($fileOrDirectoryPath, $excludedPaths);
        },
    );
}

function build($fileOrDirectory, $from, $to)
{
    if (in_array($fileOrDirectory, ['.git'])) {
        return;
    }

    $origin = $from . '/' . $fileOrDirectory;
    $destination = $to . '/' . $fileOrDirectory;

    if (is_dir($origin)) {
        umask(0);
        mkdir($destination, fileperms($origin) & 0x0FFF);
        clearstatcache();
        $filesAndDirectories = allFilesAndDirectories($origin);
        foreach ($filesAndDirectories as $fileOrDirectory) {
            build($fileOrDirectory, $origin, $destination);
        }

        return;
    }
    if (fileNeedsModification($origin)) {
        $modifiedFile = applyFileModifications($origin);
        file_put_contents($destination, $modifiedFile);
        chmod($destination, fileperms($origin) & 0x0FFF);
        clearstatcache();

        return;
    }

    intactCopy($origin, $destination);
}

function fileNeedsModification($file)
{
    return str_ends_with($file, '.php');
}

function allFilesAndDirectories($origin)
{
    $filesAndDirectories = scandir($origin);

    return array_filter($filesAndDirectories, fn ($fileOrDirectory) => ! in_array($fileOrDirectory, ['.', '..']));
}

function applyFileModifications($origin)
{
    global $projectRoot;
    global $setting;
    global $lockSetting;

    $requireStatements = [];
    $namespaces = [];
    array_walk($setting['map'], function ($source, $namespace) use (&$namespaces) {
        $namespaces[$namespace] = $source;
    });

    array_walk($lockSetting, function ($package, $namespace) use (&$namespaces, $projectRoot) {
        $packagePath = 'Packages/' . $package['owner'] . '/' . $package['repo'] . '/';
        $packageConfig = $projectRoot . $packagePath . 'build.json';
        $packageSetting = json_decode(json: file_get_contents($packageConfig), associative: true, flags: JSON_THROW_ON_ERROR);

        foreach ($packageSetting['map'] as $namespace => $source) {
            $namespaces[$namespace] = $packagePath . $source;
        }
    });

    foreach (readLines($origin) as $line) {
        foreach ($namespaces as $namespace => $source) {
            if (
                str_starts_with($line, "use $namespace")
                || str_starts_with($line, "use function $namespace")
            ) {
                $line = trim($line);
                if (str_starts_with($line, "use function $namespace")) {
                    $line = str_replace('use function ', '', $line);
                    $line = str_replace($namespace, $source, $line);
                    $function = strrpos($line, '\\');
                    $line = substr($line, 0, strlen($line)  - (strlen($line) - $function));
                    $line .= ".php";
                } else {
                    $line = str_replace('use ', '', $line);
                    $line = str_replace(';', '.php', $line);
                    $line = str_replace($namespace, $source, $line);
                }

                $path = $projectRoot . 'builds/development/' . str_replace('\\', '/', $line);

                $requireStatements[] = "require_once '$path';";
            }
        }
    }

    if (count($requireStatements) > 0) {
        return addRequires($requireStatements, $origin);
    }

    return file_get_contents($origin);
}

function addRequires($requireStatements, $file)
{
    $content = '';

    foreach (readLines($file) as $line) {
        $content .= $line;

        if (str_starts_with($line, 'namespace')) {
            $content .= PHP_EOL;
            $content .= implode(PHP_EOL, $requireStatements);
            $content .= PHP_EOL;
        }
    }

    return $content;
}

function readLines(string $source): Generator
{
    $fileHandler = @fopen($source, "r");

    if ($fileHandler) {
        while (($line = fgets($fileHandler)) !== false) {
            yield $line;
        }
        if (!feof($fileHandler)) {
            var_dump("Error: unexpected fgets() fail");
        }
        fclose($fileHandler);
    }
}

function intactCopy($origin, $destination)
{
    umask(0);
    copy($origin, $destination);
    chmod($destination, fileperms($origin) & 0x0FFF);
    clearstatcache();
}
