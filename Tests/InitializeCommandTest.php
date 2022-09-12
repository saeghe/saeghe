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

$metaContent = <<<EOD
{
    "packages": []
}

EOD;


test(
    title: 'it makes a new default config file',
    case: function () use ($initialContent, $metaContent) {
        $buildConfig = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json';
        $metaFilePath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build-lock.json';

        $output = shell_exec("{$_SERVER['PWD']}/saeghe --command=initialize --project=TestRequirements/Fixtures/EmptyProject");

        File\assert_exists($buildConfig, 'Config file does not exists: ' . $output);
        File\assert_exists($metaFilePath, 'Lock file does not exists: ' . $output);
        File\assert_content($buildConfig, $initialContent, 'Config file content is not correct after running initialize!');
        File\assert_content($metaFilePath, $metaContent, 'Lock file content is not correct after running initialize!');

        return [$buildConfig, $metaFilePath];
    },
    after: function ($buildConfig, $metaFilePath) {
        shell_exec('rm -f ' . $buildConfig);
        shell_exec('rm -f ' . $metaFilePath);
    }
);

test(
    title: 'it makes a new config file with given filename',
    case: function ($buildConfig, $configPath, $metaFile) use ($initialContent, $metaContent) {
        $output = shell_exec("{$_SERVER['PWD']}/saeghe --command=initialize --config-file=$buildConfig");

        File\assert_exists($configPath, 'Custom config file does not exists: ' . $output);
        File\assert_exists($metaFile, 'Custom lock file does not exists: ' . $output);
        File\assert_content($configPath, $initialContent, 'Custom config file content is not correct after running initialize!');
        File\assert_content($metaFile, $metaContent, 'Custom config file content is not correct after running initialize!');

        return [$configPath, $metaFile];
    },
    before: function () {
        $buildConfig = 'build-config.json';
        $metaFile = 'build-config-lock.json';
        $configPath = $_SERVER['PWD'] . '/' . $buildConfig;
        // Make sure file does not exist
        shell_exec('rm -f ' . $configPath);

        return compact('buildConfig', 'configPath', 'metaFile');
    },
    after: function ($configPath, $metaFile) {
        shell_exec('rm -f ' . $configPath);
        shell_exec('rm -f ' . $metaFile);
    }
);

test(
    title: 'it makes a new config file with given packages directory',
    case: function () use ($initialContentWithPackagesDirectory) {
        $buildConfig = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json';
        $metaFilePath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build-lock.json';

        $output = shell_exec("{$_SERVER['PWD']}/saeghe --command=initialize --project=TestRequirements/Fixtures/EmptyProject --packages-directory=vendor");

        File\assert_exists($buildConfig, 'Config file does not exists: ' . $output);
        File\assert_content($buildConfig, $initialContentWithPackagesDirectory, 'Config file content is not correct after running initialize!');

        return [$buildConfig, $metaFilePath];
    },
    after: function ($buildConfig, $metaFilePath) {
        shell_exec('rm -f ' . $buildConfig);
        shell_exec('rm -f ' . $metaFilePath);
    }
);
