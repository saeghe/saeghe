<?php

namespace Tests\InitCommandTest;

use function Saeghe\Cli\IO\Write\assert_success;
use Saeghe\TestRunner\Assertions\File;

$initialContent = <<<EOD
{
    "map": [],
    "entry-points": [],
    "excludes": [],
    "executables": [],
    "packages-directory": "Packages",
    "packages": []
}

EOD;

$initialContentWithPackagesDirectory = <<<EOD
{
    "map": [],
    "entry-points": [],
    "excludes": [],
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
    case: function ($packagesDirectory) use ($initialContent, $metaContent) {
        $configPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config.json';
        $metaFilePath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json';

        $output = shell_exec("{$_SERVER['PWD']}/saeghe init --project=TestRequirements/Fixtures/EmptyProject");

        File\assert_exists($configPath, 'Config file does not exists: ' . $output);
        File\assert_exists($packagesDirectory, 'Packages directory is not created: ' . $output);
        File\assert_content($configPath, $initialContent, 'Config file content is not correct after running init!');
        File\assert_content($metaFilePath, $metaContent, 'Lock file content is not correct after running init!');
        assert_success('Project has been initialized.', $output);
        return [$configPath, $metaFilePath, $packagesDirectory];
    },
    before: function () {
        $packagesDirectory = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages';
        shell_exec('rm -fR ' . $packagesDirectory);

        return $packagesDirectory;
    },
    after: function ($configPath, $metaFilePath, $packagesDirectory) {
        shell_exec('rm -f ' . $configPath);
        shell_exec('rm -f ' . $metaFilePath);
        shell_exec('rm -fR ' . $packagesDirectory);
    }
);

test(
    title: 'it makes a new config file with given packages directory',
    case: function () use ($initialContentWithPackagesDirectory) {
        $configPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config.json';
        $metaFilePath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json';
        $packagesDirectory = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/vendor';

        $output = shell_exec("{$_SERVER['PWD']}/saeghe init --project=TestRequirements/Fixtures/EmptyProject --packages-directory=vendor");

        File\assert_exists($packagesDirectory, 'packages directory has not been created: ' . $output);
        File\assert_exists($configPath, 'Config file does not exists: ' . $output);
        File\assert_content($configPath, $initialContentWithPackagesDirectory, 'Config file content is not correct after running init!');

        return [$configPath, $metaFilePath, $packagesDirectory];
    },
    after: function ($configPath, $metaFilePath, $packagesDirectory) {
        shell_exec('rm -f ' . $configPath);
        shell_exec('rm -f ' . $metaFilePath);
        shell_exec('rm -fR ' . $packagesDirectory);
    }
);
