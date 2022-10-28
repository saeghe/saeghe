<?php

namespace Saeghe\Saeghe\Commands\Build;

use Saeghe\Cli\IO\Write;
use Saeghe\Saeghe\Config;
use Saeghe\Saeghe\Meta;
use Saeghe\Saeghe\Package;
use Saeghe\Saeghe\Path;
use Saeghe\Saeghe\PhpFile;
use Saeghe\Saeghe\Project;
use Saeghe\Saeghe\Str;

$autoloads = [];

function run(Project $project)
{
    umask(0);

    $config = Config::fromArray(json_to_array($project->configFilePath->toString()));
    $meta = Meta::fromArray(json_to_array($project->configLockFilePath->toString()));

    dir_clean($project->buildRoot->toString());
    dir_find_or_create($project->buildRoot->append($config->packagesDirectory)->toString());

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

function make_entry_points(Project $project, Config $config, array $replaceMap, array $autoloads): void
{
    foreach ($config->entryPoints as $entrypoint) {
        add_autoloads($project->buildRoot->append($entrypoint), $replaceMap, $autoloads);
    }
}

function add_executables(Project $project, Config $config, Package $package, array $replaceMap, array $autoloads): void
{
    $packageConfig = Config::fromArray(json_to_array($package->configPath($project, $config)->toString()));
    foreach ($packageConfig->executables as $linkName => $source) {
        $target = $package->buildRoot($project, $config)->append($source);
        $link = $project->buildRoot->append($linkName);
        symlink($target->toString(), $link->toString());
        add_autoloads($target, $replaceMap, $autoloads);
        chmod($target->toString(), 0774);
    }
}

function compile_packages(Project $project, Config $config, Package $package, array $replaceMap): void
{
    dir_renew($project->buildRoot->append("{$config->packagesDirectory}/{$package->owner}/{$package->repo}")->toString());

    $filesAndDirectories = should_compile_files_and_directories_for_package($project, $config, $package);
    $packageConfig = Config::fromArray(json_to_array($package->configPath($project, $config)->toString()));
    $packageRoot = $package->root($project, $config);
    $packageBuildRoot = $package->buildRoot($project, $config);

    foreach ($filesAndDirectories as $fileOrDirectory) {
        compile($packageConfig, $packageRoot->append($fileOrDirectory), $packageBuildRoot->append($fileOrDirectory), $replaceMap);
    }
}

function compile_project_files(Project $project, Config $config, array $replaceMap): void
{
    $filesAndDirectories = should_compile_files_and_directories($project, $config);

    foreach ($filesAndDirectories as $fileOrDirectory) {
        compile($config, $project->root->append($fileOrDirectory), $project->buildRoot->append($fileOrDirectory), $replaceMap);
    }
}

function compile(Config $config, Path $origin, Path $destination, array $replaceMap): void
{
    if (is_dir($origin->toString())) {
        dir_preserve_copy($origin->toString(), $destination->toString());
        $subFilesAndDirectories = all_files_and_directories($origin->toString());
        foreach ($subFilesAndDirectories as $subFileOrDirectory) {
            compile($config, $origin->append($subFileOrDirectory), $destination->append($subFileOrDirectory), $replaceMap);
        }

        return;
    }

    if (file_needs_modification($origin, $config)) {
        compile_file($origin, $destination, $replaceMap);

        return;
    }

    intact_copy($origin->toString(), $destination->toString());
}

function compile_file(Path $origin, Path $destination, array $replaceMap): void
{
    $modifiedFile = apply_file_modifications($origin, $replaceMap);
    file_preserve_modify($origin->toString(), $destination->toString(), $modifiedFile);
}

function apply_file_modifications(Path $origin, array $replaceMap): string
{
    global $autoloads;

    $content = file_get_contents($origin->toString());
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

function path_finder(array $replaceMap, string $import, bool $absolute): ?string
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

    return $realPath ? Path::fromString($realPath)->toString() : null;
}

function add_requires_and_autoload(array $requireStatements, Path $file): string
{
    $content = '';

    $requiresAdded = false;

    foreach (read_lines($file->toString()) as $line) {
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
        foreach (read_lines($file->toString()) as $line) {
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
        $packageConfig = Config::fromArray(json_to_array($package->configPath($project, $config)->toString()));
        $packageRoot = $package->buildRoot($project, $config);

        foreach ($packageConfig->map as $namespace => $source) {
            $replaceMap[$namespace] = $packageRoot->append($source)->toString();
        }
    };

    foreach ($meta->packages as $package) {
        $mapPackageNamespaces($package);
    }

    foreach ($config->map as $namespace => $source) {
        $replaceMap[$namespace] = $project->buildRoot->append($source)->toString();
    }

    return $replaceMap;
}

function should_compile_files_and_directories_for_package(Project $project, Config $config, Package $package): array
{
    $packageConfig = Config::fromArray(json_to_array($package->configPath($project, $config)->toString()));
    $packageRoot = $package->root($project, $config);

    $excludedPaths = array_map(
        function ($excludedPath) use ($package, $packageRoot) {
            return $packageRoot->directory() . $excludedPath;
        },
        array_merge(['.', '..', '.git'], $packageConfig->excludes)
    );

    $filesAndDirectories = scandir($package->root($project, $config)->toString());

    return array_filter(
        $filesAndDirectories,
        function ($fileOrDirectory) use ($package, $excludedPaths, $packageRoot) {
            $fileOrDirectoryPath = $packageRoot->directory() . $fileOrDirectory;
            return ! in_array($fileOrDirectoryPath, $excludedPaths);
        },
    );
}

function should_compile_files_and_directories(Project $project, Config $config): array
{
    $excludedPaths = array_map(
        function ($excludedPath) use ($project) {
            return $project->root->directory() . $excludedPath;
        },
        array_merge(['.', '..', 'builds', '.git', '.idea', $config->packagesDirectory], $config->excludes)
    );

    $filesAndDirectories = scandir($project->root->toString());

    return array_filter(
        $filesAndDirectories,
        function ($fileOrDirectory) use ($project, $excludedPaths) {
            $fileOrDirectoryPath = $project->root->directory() . $fileOrDirectory;
            return ! in_array($fileOrDirectoryPath, $excludedPaths);
        },
    );
}

function file_needs_modification(Path $file, Config $config): bool
{
    return array_reduce(
            array: array_merge(array_values($config->executables), $config->entryPoints),
            callback: fn ($carry, $entryPoint) => str_ends_with($file->toString(), $entryPoint) || $carry,
            initial: false
        )
        || str_ends_with($file->toString(), '.php');
}

function add_autoloads(Path $target, array $replaceMap, array $autoloads): void
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
        '            $realPath = str_replace("\\\", DIRECTORY_SEPARATOR, $realPath) . \'.php\';',
        '            require $realPath;',
        '            return ;',
        '        }',
        '    }',
        '});',
    ]);

    $lines = explode(PHP_EOL, file_get_contents($target->toString()));
    $number = 1;
    foreach ($lines as $lineNumber => $line) {
        if (str_contains($line, '<?php')) {
            $number = $lineNumber;
            break;
        }
    }

    $lines = array_insert_after($lines, $number, $autoloadLines);

    file_put_contents($target->toString(), implode(PHP_EOL, $lines));
}
