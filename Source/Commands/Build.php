<?php

namespace Saeghe\Saeghe\Commands\Build;

use Generator;

function run()
{
    global $project;
    global $projectRoot;

    $environment = getopt('', ['environment::'])['environment'] ?? 'development';

    $buildDirectory = resetEnvironmentBuildDirectory($project, $environment);
    $filesAndDirectories = findProjectFilesAndDirectoriesForBuild();

    foreach ($filesAndDirectories as $fileOrDirectory) {
        build($fileOrDirectory, $projectRoot, $buildDirectory);
    }

    addExecutables();
    buildEntryPoints($buildDirectory);
    buildPackagesEntryPoints($buildDirectory);
}

function buildPackagesEntryPoints($buildDirectory)
{
    global $projectRoot;
    global $setting;
    global $lockSetting;

    foreach ($lockSetting as $namespace => $package) {
        $packagePath = $setting['packages-directory'] . '/' . $package['owner'] . '/' . $package['repo'] . '/';
        $packageConfig = $projectRoot . $packagePath . 'build.json';
        $packageSetting = json_decode(json: file_get_contents($packageConfig), associative: true, flags: JSON_THROW_ON_ERROR);

        if (isset($packageSetting['entry-points'])) {
            foreach ($packageSetting['entry-points'] as $entrypoint) {
                $origin = $projectRoot . $packagePath . $entrypoint;
                $destination = $buildDirectory . '/' . $packagePath . $entrypoint;
                compileFile($origin, $destination);
            }
        }
    }
}

function buildEntryPoints($buildDirectory)
{
    global $projectRoot;
    global $setting;

    if (! isset($setting['entry-points'])) {
        return;
    }

    foreach ($setting['entry-points'] as $entrypoint) {
        compileFile($projectRoot . $entrypoint, $buildDirectory . '/' . $entrypoint);
    }
}

function addExecutables()
{
    global $projectRoot;
    global $setting;
    global $lockSetting;

    foreach ($lockSetting as $namespace => $package) {
        $packagePath = $setting['packages-directory'] . '/' . $package['owner'] . '/' . $package['repo'] . '/';
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

    if (file_exists($buildDirectory)) {
        shell_exec('rm -fR ' . $buildDirectory . '/*');
    } else {
        mkdir($buildDirectory, 0755, true);
    }

    return $buildDirectory;
}

function findProjectFilesAndDirectoriesForBuild()
{
    global $projectRoot;
    global $setting;

    $excludedPaths = array_map(
        function ($excludedPath) use ($projectRoot) {
            return $projectRoot . '/' . $excludedPath;
        },
        array_merge(['.', '..', 'builds', '.git'], $setting['entry-points'] ?? [])
    );

    $filesAndDirectories = scandir($projectRoot);

    return array_filter(
        $filesAndDirectories,
        function ($fileOrDirectory) use ($projectRoot, $excludedPaths) {
            $fileOrDirectoryPath = $projectRoot . '/' . $fileOrDirectory;
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
        compileFile($origin, $destination);

        return;
    }

    intactCopy($origin, $destination);
}

function compileFile($origin, $destination)
{
    $modifiedFile = applyFileModifications($origin);
    file_put_contents($destination, $modifiedFile);
    chmod($destination, fileperms($origin) & 0x0FFF);
    clearstatcache();
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

    array_walk($lockSetting, function ($package, $namespace) use (&$namespaces, $projectRoot, $setting) {
        $packagePath = $setting['packages-directory'] . '/' . $package['owner'] . '/' . $package['repo'] . '/';
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

    $requiresAdded = false;

    foreach (readLines($file) as $line) {
        $content .= $line;

        if (str_starts_with($line, 'namespace')) {
            $requiresAdded = true;
            $content .= PHP_EOL;
            $content .= implode(PHP_EOL, $requireStatements);
            $content .= PHP_EOL;
        }
    }

    if (! $requiresAdded) {
        $content = '';
        foreach (readLines($file) as $line) {
            $content .= $line;

            if (! $requiresAdded && str_starts_with($line, '<?php')) {
                $requiresAdded = true;
                $content .= PHP_EOL;
                $content .= implode(PHP_EOL, $requireStatements);
                $content .= PHP_EOL;
            }
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
