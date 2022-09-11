<?php

namespace Tests\InitializeCommandTest;

use Saeghe\TestRunner\Assertions\File;

$initialContent = <<<EOD
{
    "map": [],
    "entry-points": [],
    "executables": [],
    "packages-directory": "Packages",
    "packages": []
}

EOD;

$initialContentWithPackagesDirectory = <<<EOD
{
    "map": [],
    "entry-points": [],
    "executables": [],
    "packages-directory": "vendor",
    "packages": []
}

EOD;

$lockContent = <<<EOD
{
    "packages": []
}

EOD;


test(
    title: 'it makes a new default config file',
    case: function () use ($initialContent, $lockContent) {
        $buildConfig = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json';
        $lockPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build-lock.json';

        $output = shell_exec("{$_SERVER['PWD']}/saeghe --command=initialize --project=TestRequirements/Fixtures/EmptyProject");

        File\assertExists($buildConfig, 'Config file does not exists: ' . $output);
        File\assertExists($lockPath, 'Lock file does not exists: ' . $output);
        File\assertContent($buildConfig, $initialContent, 'Config file content is not correct after running initialize!');
        File\assertContent($lockPath, $lockContent, 'Lock file content is not correct after running initialize!');

        return [$buildConfig, $lockPath];
    },
    after: function ($buildConfig, $lockPath) {
        shell_exec('rm -f ' . $buildConfig);
        shell_exec('rm -f ' . $lockPath);
    }
);

test(
    title: 'it makes a new config file with given filename',
    case: function ($buildConfig, $configPath, $lockPath) use ($initialContent, $lockContent) {
        $output = shell_exec("{$_SERVER['PWD']}/saeghe --command=initialize --config=$buildConfig");

        File\assertExists($configPath, 'Custom config file does not exists: ' . $output);
        File\assertExists($lockPath, 'Custom lock file does not exists: ' . $output);
        File\assertContent($configPath, $initialContent, 'Custom config file content is not correct after running initialize!');
        File\assertContent($lockPath, $lockContent, 'Custom config file content is not correct after running initialize!');

        return [$configPath, $lockPath];
    },
    before: function () {
        $buildConfig = 'build-config.json';
        $lockPath = 'build-config-lock.json';
        $configPath = $_SERVER['PWD'] . '/' . $buildConfig;
        // Make sure file does not exist
        shell_exec('rm -f ' . $configPath);

        return compact('buildConfig', 'configPath', 'lockPath');
    },
    after: function ($configPath, $lockPath) {
        shell_exec('rm -f ' . $configPath);
        shell_exec('rm -f ' . $lockPath);
    }
);

test(
    title: 'it makes a new config file with given packages directory',
    case: function () use ($initialContentWithPackagesDirectory) {
        $buildConfig = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json';
        $lockPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build-lock.json';

        $output = shell_exec("{$_SERVER['PWD']}/saeghe --command=initialize --project=TestRequirements/Fixtures/EmptyProject --packages-directory=vendor");

        File\assertExists($buildConfig, 'Config file does not exists: ' . $output);
        File\assertContent($buildConfig, $initialContentWithPackagesDirectory, 'Config file content is not correct after running initialize!');

        return [$buildConfig, $lockPath];
    },
    after: function ($buildConfig, $lockPath) {
        shell_exec('rm -f ' . $buildConfig);
        shell_exec('rm -f ' . $lockPath);
    }
);
