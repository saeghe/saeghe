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

    $buildDirectory = find_or_create_build_directory($projectRoot, $environment);
    $packagesBuildDirectory = find_or_create_packages_build_directory($buildDirectory, $setting);
    $replaceMap = make_replace_map($setting, $lockSetting, $buildDirectory, $packagesDirectory, $packagesBuildDirectory);
    compile_packages($packagesDirectory, $packagesBuildDirectory, $lockSetting, $replaceMap);
    compile_project_files($projectRoot, $buildDirectory, $packagesBuildDirectory, $setting, $lockSetting, $replaceMap);
    add_executables($buildDirectory, $packagesBuildDirectory, $lockSetting);

    Write\success('Build finished successfully.');
}

function add_executables($buildDirectory, $packagesBuildDirectory, $packages)
{
    foreach ($packages['packages'] as $package => $meta) {
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

function compile_packages($packagesDirectory, $packagesBuildDirectory, $packages, $replaceMap)
{
    foreach ($packages['packages'] as $package => $meta) {
        $packageDirectory = $packagesDirectory . $meta['owner'] . '/' . $meta['repo'] . '/';
        $packageBuildDirectory = $packagesBuildDirectory . $meta['owner'] . '/' . $meta['repo'] . '/';
        if (! file_exists($packageBuildDirectory)) {
            mkdir($packageBuildDirectory, 0755, true);
        }
        $packageConfig = $packageDirectory . '/build.json';

        $packageSetting = file_exists($packageConfig)
            ? json_decode(json: file_get_contents($packageConfig), associative: true, flags: JSON_THROW_ON_ERROR)
            : ['map' => [], 'packages' => []];

        $filesAndDirectories = should_compile_files_and_directories($packageDirectory, $packageSetting);

        foreach ($filesAndDirectories as $fileOrDirectory) {
            compile($fileOrDirectory, $packageDirectory, $packageBuildDirectory, $replaceMap, $packageSetting);
        }
    }
}

function compile_project_files($projectRoot, $buildDirectory, $packagesBuildDirectory, $setting, $lockSetting, $replaceMap)
{
    $filesAndDirectories = should_compile_files_and_directories($projectRoot, $setting);

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
        $filesAndDirectories = all_files_and_directories($origin);
        foreach ($filesAndDirectories as $fileOrDirectory) {
            compile($fileOrDirectory, $origin, $destination, $replaceMap, $setting);
        }

        return;
    }

    if (file_needs_modification($origin, $setting)) {
        compile_file($origin, $destination, $replaceMap);

        return;
    }

    intact_copy($origin, $destination);
}

function compile_file($origin, $destination, $replaceMap)
{
    $modifiedFile = apply_file_modifications($origin, $replaceMap);
    file_put_contents($destination, $modifiedFile);
    chmod($destination, fileperms($origin) & 0x0FFF);
    clearstatcache();
}

function apply_file_modifications($origin, $replaceMap)
{
    $requireStatements = [];

    foreach (read_lines($origin) as $line) {
        if (str_starts_with($line, 'use ')) {
            $requireStatements = array_merge($requireStatements, find_require_paths($line, $replaceMap));
        }
    }

    if (count($requireStatements) > 0) {
        $requireStatements = array_map(fn ($path) => "require_once '$path';", $requireStatements);

        return add_requires($requireStatements, $origin);
    }

    return file_get_contents($origin);
}

function find_require_paths($line, $replaceMap)
{
    $detectedPaths = [];

    $possibleUses = explode('use ', $line);

    if (count($possibleUses) > 1) {
        foreach ($possibleUses as $separateUse) {
            $detectedPaths = array_merge($detectedPaths, find_require_paths($separateUse, $replaceMap));
        }

        return $detectedPaths;
    }

    $line = trim('use ' . $possibleUses[0]);

    if (str_contains($line, '\{')) {
        $parts = string_between($line, '{', '}');
        $parts = explode(',', $parts);
        $commonPart = explode('\{', $line)[0];
        $line = '';
        foreach ($parts as $part) {
            $part = trim($part);
            $line .= "use $commonPart\\$part;";
        }

        return find_require_paths($line, $replaceMap);
    }

    $statements = explode(',', str_replace('use ', '', str_replace(';', '', $line)));

    if (count($statements) > 1) {
        $line = '';
        foreach ($statements as $statement) {
            $statement = trim($statement);
            $line .= "use $statement;";
        }

        return find_require_paths($line, $replaceMap);
    }

    if (str_contains($line, ' as ')) {
        $line = explode(' as ', $line)[0] . ';';
    }

    foreach ($replaceMap as $namespace => $path) {
        if (str_starts_with($line, "use function $namespace")) {
            $line = str_replace("use function $namespace", $path, $line);
            $function = strrpos($line, '\\');
            $line = substr($line, 0, strlen($line) - (strlen($line) - $function));
            $line .= ".php";

            $detectedPaths[] = str_replace('\\', '/', $line);
            break;
        } else if (str_starts_with($line, "use const $namespace")) {
            $line = str_replace("use const $namespace", $path, $line);
            $function = strrpos($line, '\\');
            $line = substr($line, 0, strlen($line)  - (strlen($line) - $function));
            $line .= ".php";

            $detectedPaths[] = str_replace('\\', '/', $line);
            break;
        } else if (str_starts_with($line, "use $namespace")) {
            $line = str_replace("use $namespace", $path, $line);
            $line = str_replace(';', '.php', $line);

            $detectedPaths[] = str_replace('\\', '/', $line);
            break;
        }
    }

    return $detectedPaths;
}

function add_requires($requireStatements, $file)
{
    $content = '';

    $requiresAdded = false;

    foreach (read_lines($file) as $line) {
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
        foreach (read_lines($file) as $line) {
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

function make_replace_map($setting, $lockSetting, $buildDirectory, $packagesDirectory, $packagesBuildDirectory)
{
    $replaceMap = [];

    foreach ($lockSetting['packages'] as $package => $meta) {
        $packageSource = $packagesDirectory . $meta['owner'] . '/' . $meta['repo'] . '/';
        $packageBuild = $packagesBuildDirectory . $meta['owner'] . '/' . $meta['repo'] . '/';
        $packageConfig =  $packageSource . 'build.json';
        $packageLockFile =  $packageSource . 'build-lock.json';
        $subSetting = file_exists($packageConfig)
            ? json_decode(json: file_get_contents($packageConfig), associative: true, flags: JSON_THROW_ON_ERROR)
            : ['map' => [], 'packages' => []];
        $subLockSetting = file_exists($packageLockFile)
            ? json_decode(json: file_get_contents($packageLockFile), associative: true, flags: JSON_THROW_ON_ERROR)
            : [];

        $subLockSetting['packages'] = $subLockSetting['packages'] ?? [];

        $replaceMap = array_merge(
            $replaceMap,
            make_replace_map($subSetting, $subLockSetting, $packageBuild, $packagesDirectory, $packagesBuildDirectory)
        );
    }

    array_walk($setting['map'], function ($source, $namespace) use (&$replaceMap, $buildDirectory) {
        $replaceMap[$namespace] = $buildDirectory . $source;
    });

    return $replaceMap;
}

function should_compile_files_and_directories($path, $setting)
{
    $settingExcludes = $setting['excludes'] ?? [];
    $excludedPaths = array_map(
        function ($excludedPath) use ($path) {
            return $path . $excludedPath;
        },
        array_merge(['.', '..', 'builds', '.git', 'Packages', '.idea'], $settingExcludes)
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

function find_or_create_packages_build_directory($buildDirectory, $setting)
{
    $packagesBuildDirectory = $buildDirectory . $setting['packages-directory'];

    if (! file_exists($packagesBuildDirectory)) {
        mkdir($packagesBuildDirectory);
    }

    return $packagesBuildDirectory . '/';
}

function find_or_create_build_directory($projectRoot, $environment)
{
    $buildDirectory = $projectRoot . 'builds/' . $environment . '/';

    if (! file_exists($buildDirectory)) {
        mkdir($buildDirectory, 0755, true);
    } else {
        shell_exec("rm -fR $buildDirectory*");
    }

    return $buildDirectory;
}

function file_needs_modification($file, $setting)
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

function all_files_and_directories($origin)
{
    $filesAndDirectories = scandir($origin);

    return array_filter($filesAndDirectories, fn ($fileOrDirectory) => ! in_array($fileOrDirectory, ['.', '..']));
}

function read_lines(string $source): Generator
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

function intact_copy($origin, $destination)
{
    umask(0);
    copy($origin, $destination);
    chmod($destination, fileperms($origin) & 0x0FFF);
    clearstatcache();
}

function string_between($string, $start, $end)
{
    $startPosition = stripos($string, $start);
    $first = substr($string, $startPosition);
    $second = substr($first, strlen($start));
    $positionEnd = stripos($second, $end);
    $final = substr($second, 0, $positionEnd);

    return trim($final);
}
