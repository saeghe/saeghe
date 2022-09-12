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
        $configPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config.json';
        $metaFilePath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json';

        $output = shell_exec("{$_SERVER['PWD']}/saeghe --command=initialize --project=TestRequirements/Fixtures/EmptyProject");

        File\assert_exists($configPath, 'Config file does not exists: ' . $output);
        File\assert_exists($metaFilePath, 'Lock file does not exists: ' . $output);
        File\assert_content($configPath, $initialContent, 'Config file content is not correct after running initialize!');
        File\assert_content($metaFilePath, $metaContent, 'Lock file content is not correct after running initialize!');

        return [$configPath, $metaFilePath];
    },
    after: function ($configPath, $metaFilePath) {
        shell_exec('rm -f ' . $configPath);
        shell_exec('rm -f ' . $metaFilePath);
    }
);

test(
    title: 'it makes a new config file with given filename',
    case: function ($configFile, $configPath, $metaFile) use ($initialContent, $metaContent) {
        $output = shell_exec("{$_SERVER['PWD']}/saeghe --command=initialize --config-file=$configFile");

        File\assert_exists($configPath, 'Custom config file does not exists: ' . $output);
        File\assert_exists($metaFile, 'Custom lock file does not exists: ' . $output);
        File\assert_content($configPath, $initialContent, 'Custom config file content is not correct after running initialize!');
        File\assert_content($metaFile, $metaContent, 'Custom config file content is not correct after running initialize!');

        return [$configPath, $metaFile];
    },
    before: function () {
        $configFile = 'build-config.json';
        $metaFile = 'build-config-lock.json';
        $configPath = $_SERVER['PWD'] . '/' . $configFile;
        // Make sure file does not exist
        shell_exec('rm -f ' . $configPath);

        return compact('configFile', 'configPath', 'metaFile');
    },
    after: function ($configPath, $metaFile) {
        shell_exec('rm -f ' . $configPath);
        shell_exec('rm -f ' . $metaFile);
    }
);

test(
    title: 'it makes a new config file with given packages directory',
    case: function () use ($initialContentWithPackagesDirectory) {
        $configPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config.json';
        $metaFilePath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json';

        $output = shell_exec("{$_SERVER['PWD']}/saeghe --command=initialize --project=TestRequirements/Fixtures/EmptyProject --packages-directory=vendor");

        File\assert_exists($configPath, 'Config file does not exists: ' . $output);
        File\assert_content($configPath, $initialContentWithPackagesDirectory, 'Config file content is not correct after running initialize!');

        return [$configPath, $metaFilePath];
    },
    after: function ($configPath, $metaFilePath) {
        shell_exec('rm -f ' . $configPath);
        shell_exec('rm -f ' . $metaFilePath);
    }
);
