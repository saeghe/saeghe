<?php

namespace Saeghe\Saeghe\Commands\Build;

use Saeghe\Cli\IO\Write;
use function Saeghe\Cli\IO\Read\argument;

function run()
{
    global $projectRoot;
    global $config;
    global $meta;
    global $packagesDirectory;
    global $buildsPath;
    $environment = argument('environment', 'development');

    umask(0);
    $buildDirectory = dir_clean($buildsPath . $environment);
    $packagesBuildDirectory = dir_find_or_create($buildDirectory . $config['packages-directory']);
    $replaceMap = make_replace_map($config, $meta, $buildDirectory, $packagesDirectory, $packagesBuildDirectory);
    compile_packages($packagesDirectory, $packagesBuildDirectory, $meta, $replaceMap);
    compile_project_files($projectRoot, $buildDirectory, $packagesBuildDirectory, $config, $meta, $replaceMap);
    add_executables($buildDirectory, $packagesBuildDirectory, $meta);

    clearstatcache();

    Write\success('Build finished successfully.');
}

function add_executables($buildDirectory, $packagesBuildDirectory, $meta)
{
    foreach ($meta['packages'] ?? [] as $package => $packageMeta) {
        $packageBuildDirectory = $packagesBuildDirectory . $packageMeta['owner'] . '/' . $packageMeta['repo'] . '/';
        $packageConfigPath = $packageBuildDirectory . '/' . DEFAULT_CONFIG_FILENAME;
        $packageConfig = json_to_array($packageConfigPath);

        if (isset($packageConfig['executables'])) {
            foreach ($packageConfig['executables'] as $linkName => $source) {
                $target = $packageBuildDirectory . $source;
                $link = $buildDirectory . $linkName;
                symlink($target, $link);
            }
        }
    }
}

function compile_packages($packagesDirectory, $packagesBuildDirectory, $packages, $replaceMap)
{
    foreach ($packages['packages'] as $package => $meta) {
        $packageDirectory = $packagesDirectory . $meta['owner'] . '/' . $meta['repo'] . '/';
        $packageBuildDirectory = dir_renew($packagesBuildDirectory . $meta['owner'] . '/' . $meta['repo'] . '/');

        $packageConfig = array_merge_json(
            ['map' => [], 'packages' => [], 'excludes' => [], 'executables' => [], 'entry-points' => []],
            $packageDirectory . '/' . DEFAULT_CONFIG_FILENAME,
        );

        $filesAndDirectories = should_compile_files_and_directories_for_package($packageDirectory, $packageConfig);

        foreach ($filesAndDirectories as $fileOrDirectory) {
            compile($fileOrDirectory, $packageDirectory, $packageBuildDirectory, $replaceMap, $packageConfig);
        }
    }
}

function compile_project_files($projectRoot, $buildDirectory, $packagesBuildDirectory, $config, $meta, $replaceMap)
{
    $filesAndDirectories = should_compile_files_and_directories($projectRoot, $config);

    foreach ($filesAndDirectories as $fileOrDirectory) {
        compile($fileOrDirectory, $projectRoot, $buildDirectory, $replaceMap, $config);
    }
}

function compile($fileOrDirectory, $from, $to, $replaceMap, $config)
{
    $origin = $from . (str_ends_with($from, '/') ? '' : '/') . $fileOrDirectory;
    $destination = $to . (str_ends_with($to, '/') ? '' : '/') . $fileOrDirectory;

    if (is_dir($origin)) {
        dir_preserve_copy($origin, $destination);
        $filesAndDirectories = all_files_and_directories($origin);
        foreach ($filesAndDirectories as $fileOrDirectory) {
            compile($fileOrDirectory, $origin, $destination, $replaceMap, $config);
        }

        return;
    }

    if (file_needs_modification($origin, $config)) {
        compile_file($origin, $destination, $replaceMap);

        return;
    }

    intact_copy($origin, $destination);
}

function compile_file($origin, $destination, $replaceMap)
{
    $modifiedFile = apply_file_modifications($origin, $replaceMap);
    file_preserve_modify($origin, $destination, $modifiedFile);
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

function make_replace_map($config, $meta, $buildDirectory, $packagesDirectory, $packagesBuildDirectory)
{
    $replaceMap = [];

    foreach ($meta['packages'] as $package => $packageMeta) {
        $packageSource = $packagesDirectory . $packageMeta['owner'] . '/' . $packageMeta['repo'] . '/';
        $packageBuild = $packagesBuildDirectory . $packageMeta['owner'] . '/' . $packageMeta['repo'] . '/';
        $packageConfigPath =  $packageSource . DEFAULT_CONFIG_FILENAME;
        $packageMetaFilename = str_replace('.json', '-lock.json', DEFAULT_CONFIG_FILENAME);
        $metaPath =  $packageSource . $packageMetaFilename;
        $packageConfig = json_to_array($packageConfigPath, ['map' => [], 'packages' => []]);
        $subPackageMeta = json_to_array($metaPath, []);

        $subPackageMeta['packages'] = $subPackageMeta['packages'] ?? [];

        $replaceMap = array_merge(
            $replaceMap,
            make_replace_map($packageConfig, $subPackageMeta, $packageBuild, $packagesDirectory, $packagesBuildDirectory)
        );
    }

    array_walk($config['map'], function ($source, $namespace) use (&$replaceMap, $buildDirectory) {
        $replaceMap[$namespace] = $buildDirectory . $source;
    });

    return $replaceMap;
}

function should_compile_files_and_directories_for_package($path, $config)
{
    $excludedPaths = array_map(
        function ($excludedPath) use ($path) {
            return $path . $excludedPath;
        },
        array_merge(['.', '..', '.git'], $config['excludes'])
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

function should_compile_files_and_directories($path, $config)
{
    $excludedPaths = array_map(
        function ($excludedPath) use ($path) {
            return $path . $excludedPath;
        },
        array_merge(['.', '..', 'builds', '.git', '.idea', $config['packages-directory']], $config['excludes'])
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

function file_needs_modification($file, $config)
{
    return array_reduce(
            array: array_merge(array_values($config['executables']), $config['entry-points']),
            callback: fn ($carry, $entryPoint) => str_ends_with($file, $entryPoint) || $carry,
            initial: false
        )
        || str_ends_with($file, '.php');
}
