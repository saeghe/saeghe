<?php

namespace Saeghe\Saeghe\Commands\Build;

use Saeghe\Cli\IO\Write;
use Saeghe\Saeghe\Config;
use Saeghe\Saeghe\Meta;
use Saeghe\Saeghe\Package;
use Saeghe\Saeghe\PhpFile;
use Saeghe\Saeghe\Project;
use Saeghe\Saeghe\Str;

$autoloads = [];

function run(Project $project)
{
    umask(0);

    $config = Config::fromArray(json_to_array($project->configFilePath));
    $meta = Meta::fromArray(json_to_array($project->configLockFilePath));

    dir_clean($project->buildRoot);
    dir_find_or_create($project->buildRoot . $config->packagesDirectory);

    $replaceMap = make_replace_map($project, $config, $meta);

    foreach ($meta->packages as $package) {
        compile_packages($project, $config, $package, $replaceMap);
    }

    compile_project_files($project, $config, $replaceMap);

    global $autoloads;
    make_entry_points($project, $config, $replaceMap, $autoloads);

    foreach ($meta->packages as $package) {
        add_executables($project, $config, $package, $replaceMap, $autoloads);
    }

    clearstatcache();

    Write\success('Build finished successfully.');
}

function make_entry_points(Project $project, Config $config, $replaceMap, $autoloads)
{
    foreach ($config->entryPoints as $entrypoint) {
        $path = $project->buildRoot . $entrypoint;
        add_autoloads($path, $replaceMap, $autoloads);
    }
}

function add_executables(Project $project, Config $config, Package $package, $replaceMap, $autoloads)
{
    $packageConfig = Config::fromArray(json_to_array($package->configPath($project, $config)));
    foreach ($packageConfig->executables as $linkName => $source) {
        $target = $package->buildRoot($project, $config) . $source;
        $link = $project->buildRoot . $linkName;
        symlink($target, $link);
        add_autoloads($target, $replaceMap, $autoloads);
        chmod($target, 0774);
    }
}

function compile_packages(Project $project, Config $config, Package $package, $replaceMap)
{
    dir_renew($project->buildRoot . '/' . $config->packagesDirectory . '/' . $package->owner . '/' . $package->repo);

    $filesAndDirectories = should_compile_files_and_directories_for_package($project, $config, $package);
    $packageConfig = Config::fromArray(json_to_array($package->configPath($project, $config)));
    $packageRoot = $package->root($project, $config);
    $packageBuildRoot = $package->buildRoot($project, $config);

    foreach ($filesAndDirectories as $fileOrDirectory) {
        compile($packageConfig, $packageRoot . $fileOrDirectory, $packageBuildRoot . $fileOrDirectory, $replaceMap);
    }
}

function compile_project_files(Project $project, Config $config, $replaceMap)
{
    $filesAndDirectories = should_compile_files_and_directories($project, $config);

    foreach ($filesAndDirectories as $fileOrDirectory) {
        compile($config, $project->root . $fileOrDirectory, $project->buildRoot . $fileOrDirectory, $replaceMap);
    }
}

function compile(Config $config, $origin, $destination, $replaceMap)
{
    if (is_dir($origin)) {
        dir_preserve_copy($origin, $destination);
        $subFilesAndDirectories = all_files_and_directories($origin);
        foreach ($subFilesAndDirectories as $subFileOrDirectory) {
            compile($config, $origin . '/' . $subFileOrDirectory, $destination . '/' . $subFileOrDirectory, $replaceMap);
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
    global $autoloads;

    $content = file_get_contents($origin);
    $phpFile = new PhpFile($content);

    $imports = array_merge(
        $phpFile->usedConstants(),
        array_keys($phpFile->importedConstants()),
        $phpFile->usedFunctions(),
        array_keys($phpFile->importedFunctions()),
    );
    $autoload = array_merge(
        $phpFile->extendedClasses(),
        $phpFile->implementedInterfaces(),
        $phpFile->usedTraits(),
        $phpFile->usedClasses(),
        array_keys($phpFile->importedClasses()),
    );

    $requireStatements = [];

     array_walk($imports, function ($import) use ($replaceMap, &$requireStatements) {
        $path = path_finder($replaceMap, $import, true);
        if (! $path) {
            $path = path_finder($replaceMap, Str\before_last_occurrence($import, '\\'), false);
        }

        $requireStatements[$import] = $path;
    });

    $autoloadMap = [];
    array_walk($autoload, function ($import) use ($replaceMap, &$autoloadMap) {
        $path = path_finder($replaceMap, $import, false);
        $autoloadMap[$import] = $path;
    });

    $autoloads = array_merge(
        $autoloads,
        array_unique(array_filter($autoloadMap))
    );

    $requireStatements = array_unique(array_filter($requireStatements));

    if (count($requireStatements)) {
        $requireStatements = array_map(fn($path) => "require_once '$path';", $requireStatements);

        return add_requires_and_autoload($requireStatements, $origin);
    }

    return $content;
}

function path_finder($replaceMap, $import, $absolute)
{
    $realPath = null;

    foreach ($replaceMap as $namespace => $path) {
        if ($absolute) {
            if ($import === $namespace && str_ends_with($path, '.php')) {
                return $path;
            }
        } else {
            if (str_starts_with($import, $namespace)) {
                $pos = strpos($import, $namespace);
                if ($pos !== false) {
                    $realPath = substr_replace($import, $path, $pos, strlen($namespace));
                }
                $realPath = str_replace('\\', '/', $realPath) . '.php';
            }
        }
    }

    return $realPath;
}

function add_requires_and_autoload($requireStatements, $file)
{
    $content = '';

    $requiresAdded = false;

    foreach (read_lines($file) as $line) {
        $content .= $line;

        if (str_starts_with($line, 'namespace')) {
            $requiresAdded = true;
            if (count($requireStatements) > 0) {
                $content .= PHP_EOL;
                $content .= implode(PHP_EOL, $requireStatements);
                $content .= PHP_EOL;
            }
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

function make_replace_map(Project $project, Config $config, Meta $meta): array
{
    $replaceMap = [];

    $mapPackageNamespaces = function (Package $package) use (&$replaceMap, $project, $config) {
        $packageConfig = Config::fromArray(json_to_array($package->configPath($project, $config)));
        $packageRoot = $package->buildRoot($project, $config);

        foreach ($packageConfig->map as $namespace => $source) {
            $replaceMap[$namespace] = $packageRoot . $source;
        }
    };

    foreach ($meta->packages as $package) {
        $mapPackageNamespaces($package);
    }

    foreach ($config->map as $namespace => $source) {
        $replaceMap[$namespace] = $project->buildRoot . $source;
    }

    return $replaceMap;
}

function should_compile_files_and_directories_for_package(Project $project, Config $config, Package $package)
{
    $packageConfig = Config::fromArray(json_to_array($package->configPath($project, $config)));
    $packageRoot = $package->root($project, $config);

    $excludedPaths = array_map(
        function ($excludedPath) use ($package, $packageRoot) {
            return $packageRoot . $excludedPath;
        },
        array_merge(['.', '..', '.git'], $packageConfig->excludes)
    );

    $filesAndDirectories = scandir($package->root($project, $config));

    return array_filter(
        $filesAndDirectories,
        function ($fileOrDirectory) use ($package, $excludedPaths, $packageRoot) {
            $fileOrDirectoryPath = $packageRoot . $fileOrDirectory;
            return ! in_array($fileOrDirectoryPath, $excludedPaths);
        },
    );
}

function should_compile_files_and_directories(Project $project, Config $config)
{
    $excludedPaths = array_map(
        function ($excludedPath) use ($project) {
            return $project->root . $excludedPath;
        },
        array_merge(['.', '..', 'builds', '.git', '.idea', $config->packagesDirectory], $config->excludes)
    );

    $filesAndDirectories = scandir($project->root);

    return array_filter(
        $filesAndDirectories,
        function ($fileOrDirectory) use ($project, $excludedPaths) {
            $fileOrDirectoryPath = $project->root . $fileOrDirectory;
            return ! in_array($fileOrDirectoryPath, $excludedPaths);
        },
    );
}

function file_needs_modification($file, Config $config)
{
    return array_reduce(
            array: array_merge(array_values($config->executables), $config->entryPoints),
            callback: fn ($carry, $entryPoint) => str_ends_with($file, $entryPoint) || $carry,
            initial: false
        )
        || str_ends_with($file, '.php');
}

function add_autoloads($target, $replaceMap, $autoloads)
{
    $autoloadLines = [];

    $autoloadLines = array_merge($autoloadLines, [
        '',
        'spl_autoload_register(function ($class) {',
        '    $classes = [',
    ]);

    foreach ($autoloads as $class => $path) {
        $autoloadLines[] = "        '$class' => '$path',";
    }

    $autoloadLines = array_merge($autoloadLines, [
        '    ];',
        '',
        '    if (array_key_exists($class, $classes)) {',
        '        require $classes[$class];',
        '    }',
        '',
        '}, true, true);',
    ]);

    $autoloadLines = array_merge($autoloadLines, [
        '',
        'spl_autoload_register(function ($class) {',
        '    $namespaces = [',
    ]);

    foreach ($replaceMap as $namespace => $path) {
        $autoloadLines[] = "        '$namespace' => '$path',";
    }

    $autoloadLines = array_merge($autoloadLines, [
        '    ];',
        '',
        '    $realPath = null;',
        '',
        '    foreach ($namespaces as $namespace => $path) {',
        '        if (str_starts_with($class, $namespace)) {',
        '            $pos = strpos($class, $namespace);',
        '            if ($pos !== false) {',
        '                $realPath = substr_replace($class, $path, $pos, strlen($namespace));',
        '            }',
        '            $realPath = str_replace("\\\", "/", $realPath) . \'.php\';',
        '            require $realPath;',
        '            return ;',
        '        }',
        '    }',
        '});',
    ]);

    $lines = explode(PHP_EOL, file_get_contents($target));
    $number = 1;
    foreach ($lines as $lineNumber => $line) {
        if (str_contains($line, '<?php')) {
            $number = $lineNumber;
            break;
        }
    }

    $lines = array_insert_after($lines, $number, $autoloadLines);

    file_put_contents($target, implode(PHP_EOL, $lines));
}
