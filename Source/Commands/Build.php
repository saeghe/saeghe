<?php

namespace Saeghe\Saeghe\Commands\Build;

use Generator;
use Saeghe\Cli\IO\Write;
use function Saeghe\Cli\IO\Read\argument;

function run()
{
    global $projectRoot;
    global $setting;
    global $lockSetting;
    global $packagesDirectory;
    $environment = argument('environment', 'development');

    $buildDirectory = findOrCreateBuildDirectory($projectRoot, $environment);
    $packagesBuildDirectory = findOrCreatePackagesBuildDirectory($buildDirectory, $setting);
    $replaceMap = makeReplaceMap($setting, $lockSetting, $buildDirectory, $packagesDirectory, $packagesBuildDirectory);
    compilePackages($packagesDirectory, $packagesBuildDirectory, $lockSetting, $replaceMap);
    compileProjectFiles($projectRoot, $buildDirectory, $packagesBuildDirectory, $setting, $lockSetting, $replaceMap);
    addExecutables($buildDirectory, $packagesBuildDirectory, $lockSetting);

    Write\success('Build finished successfully.');
}

function addExecutables($buildDirectory, $packagesBuildDirectory, $packages)
{
    foreach ($packages as $package => $meta) {
        $packageBuildDirectory = $packagesBuildDirectory . $meta['owner'] . '/' . $meta['repo'] . '/';
        $packageConfig = $packageBuildDirectory . '/build.json';
        if (file_exists($packageConfig)) {
            $packageSetting = json_decode(json: file_get_contents($packageConfig), associative: true, flags: JSON_THROW_ON_ERROR);

            if (isset($packageSetting['executables'])) {
                foreach ($packageSetting['executables'] as $linkName => $source) {
                    $target = $packageBuildDirectory . $source;
                    $link = $buildDirectory . $linkName;
                    symlink($target, $link);
                }
            }
        }
    }
}

function compilePackages($packagesDirectory, $packagesBuildDirectory, $packages, $replaceMap)
{
    foreach ($packages as $package => $meta) {
        $packageDirectory = $packagesDirectory . $meta['owner'] . '/' . $meta['repo'] . '/';
        $packageBuildDirectory = $packagesBuildDirectory . $meta['owner'] . '/' . $meta['repo'] . '/';
        if (! file_exists($packageBuildDirectory)) {
            mkdir($packageBuildDirectory, 0755, true);
        }
        $packageConfig = $packageDirectory . '/build.json';

        $packageSetting = file_exists($packageConfig)
            ? json_decode(json: file_get_contents($packageConfig), associative: true, flags: JSON_THROW_ON_ERROR)
            : ['map' => [], 'packages' => []];

        $filesAndDirectories = shouldCompileFilesAndDirectories($packageDirectory, $packageSetting);

        foreach ($filesAndDirectories as $fileOrDirectory) {
            compile($fileOrDirectory, $packageDirectory, $packageBuildDirectory, $replaceMap, $packageSetting);
        }

//        if (isset($packageSetting['executables'])) {
//            foreach ($packageSetting['executables'] as $symlink => $path) {
//                compile($path, $packageDirectory, $packageBuildDirectory, $replaceMap, $packageSetting);
//            }
//        }
    }
}

function compileProjectFiles($projectRoot, $buildDirectory, $packagesBuildDirectory, $setting, $lockSetting, $replaceMap)
{
    $filesAndDirectories = shouldCompileFilesAndDirectories($projectRoot, $setting);

    foreach ($filesAndDirectories as $fileOrDirectory) {
        compile($fileOrDirectory, $projectRoot, $buildDirectory, $replaceMap, $setting);
    }
}

function compile($fileOrDirectory, $from, $to, $replaceMap, $setting)
{
    $origin = $from . (str_ends_with($from, '/') ? '' : '/') . $fileOrDirectory;
    $destination = $to . (str_ends_with($to, '/') ? '' : '/') . $fileOrDirectory;

    if (is_dir($origin)) {
        umask(0);
        mkdir($destination, fileperms($origin) & 0x0FFF);
        clearstatcache();
        $filesAndDirectories = allFilesAndDirectories($origin);
        foreach ($filesAndDirectories as $fileOrDirectory) {
            compile($fileOrDirectory, $origin, $destination, $replaceMap, $setting);
        }

        return;
    }

    if (fileNeedsModification($origin, $setting)) {
        compileFile($origin, $destination, $replaceMap);

        return;
    }

    intactCopy($origin, $destination);
}

function compileFile($origin, $destination, $replaceMap)
{
    $modifiedFile = applyFileModifications($origin, $replaceMap);
    file_put_contents($destination, $modifiedFile);
    chmod($destination, fileperms($origin) & 0x0FFF);
    clearstatcache();
}

function applyFileModifications($origin, $replaceMap)
{
    $requireStatements = [];

    foreach (readLines($origin) as $line) {
        $path = findRequirePath($line, $replaceMap);
        if ($path) {
            $requireStatements[] = "require_once '$path';";
        }
    }

    if (count($requireStatements) > 0) {
        return addRequires($requireStatements, $origin);
    }

    return file_get_contents($origin);
}

function findRequirePath($line, $replaceMap)
{
    $detectedPath = null;

    foreach ($replaceMap as $namespace => $path) {
        if (
            str_starts_with($line, "use $namespace")
            || str_starts_with($line, "use function $namespace")
        ) {
            $line = trim($line);
            if (str_starts_with($line, "use function $namespace")) {
                $line = str_replace("use function $namespace", $path, $line);
                $function = strrpos($line, '\\');
                $line = substr($line, 0, strlen($line)  - (strlen($line) - $function));
                $line .= ".php";
            } else {
                $line = str_replace("use $namespace", $path, $line);
                $line = str_replace(';', '.php', $line);
            }

            $detectedPath = str_replace('\\', '/', $line);
        }
    }

    return $detectedPath;
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

function makeReplaceMap($setting, $lockSetting, $buildDirectory, $packagesDirectory, $packagesBuildDirectory)
{
    $replaceMap = [];

    foreach ($lockSetting as $namespace => $meta) {
        $packageSource = $packagesDirectory . $meta['owner'] . '/' . $meta['repo'] . '/';
        $packageBuild = $packagesBuildDirectory . $meta['owner'] . '/' . $meta['repo'] . '/';
        $packageConfig =  $packageSource . 'build.json';
        $packageLockFile =  $packageSource . 'build.lock';
        $subSetting = file_exists($packageConfig)
            ? json_decode(json: file_get_contents($packageConfig), associative: true, flags: JSON_THROW_ON_ERROR)
            : ['map' => [], 'packages' => []];
        $subLockSetting = file_exists($packageLockFile)
            ? json_decode(json: file_get_contents($packageLockFile), associative: true, flags: JSON_THROW_ON_ERROR)
            : [];

        $replaceMap = array_merge(
            $replaceMap,
            makeReplaceMap($subSetting, $subLockSetting, $packageBuild, $packagesDirectory, $packagesBuildDirectory)
        );
    }

    array_walk($setting['map'], function ($source, $namespace) use (&$replaceMap, $buildDirectory) {
        $replaceMap[$namespace] = $buildDirectory . $source;
    });

    return $replaceMap;
}

function shouldCompileFilesAndDirectories($path, $setting)
{
    $excludedPaths = array_map(
        function ($excludedPath) use ($path) {
            return $path . $excludedPath;
        },
        ['.', '..', 'builds', '.git', 'Packages', '.idea']
    );

    $filesAndDirectories = scandir($path);

    return array_filter(
        $filesAndDirectories,
        function ($fileOrDirectory) use ($path, $excludedPaths) {
            $fileOrDirectoryPath = $path . $fileOrDirectory;
            return ! in_array($fileOrDirectoryPath, $excludedPaths);
        },
    );
}

function findOrCreatePackagesBuildDirectory($buildDirectory, $setting)
{
    $packagesBuildDirectory = $buildDirectory . $setting['packages-directory'];

    if (! file_exists($packagesBuildDirectory)) {
        mkdir($packagesBuildDirectory);
    }

    return $packagesBuildDirectory . '/';
}

function findOrCreateBuildDirectory($projectRoot, $environment)
{
    $buildDirectory = $projectRoot . 'builds/' . $environment . '/';

    if (! file_exists($buildDirectory)) {
        mkdir($buildDirectory, 0755, true);
    } else {
        shell_exec("rm -fR $buildDirectory*");
    }

    return $buildDirectory;
}

function fileNeedsModification($file, $setting)
{
    $executables = isset($setting['executables']) ?  array_values($setting['executables']) : [];
    $entryPoints = $setting['entry-points'] ?? [];

    return array_reduce(
            array: array_merge($executables, $entryPoints),
            callback: fn ($carry, $entryPoint) => str_ends_with($file, $entryPoint) || $carry,
            initial: false
        )
        || str_ends_with($file, '.php');
}

function allFilesAndDirectories($origin)
{
    $filesAndDirectories = scandir($origin);

    return array_filter($filesAndDirectories, fn ($fileOrDirectory) => ! in_array($fileOrDirectory, ['.', '..']));
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
