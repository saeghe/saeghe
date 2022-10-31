<?php

namespace Tests\System\InitCommandTest;

use function Saeghe\Cli\IO\Write\assert_success;
use Saeghe\TestRunner\Assertions\File;
use function Saeghe\FileManager\Directory\flush;

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
    case: function () use ($initialContent, $metaContent) {
        $packagesDirectory = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages';
        $configPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config.json';
        $metaFilePath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json';

        $output = shell_exec("{$_SERVER['PWD']}/saeghe init --project=TestRequirements/Fixtures/EmptyProject");

        File\assert_exists($configPath, 'Config file does not exists: ' . $output);
        File\assert_exists($packagesDirectory, 'Packages directory is not created: ' . $output);
        File\assert_content($configPath, $initialContent, 'Config file content is not correct after running init!');
        File\assert_content($metaFilePath, $metaContent, 'Lock file content is not correct after running init!');
        assert_success('Project has been initialized.', $output);
    },
    after: function () {
        flush($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject');
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
        File\assert_exists($metaFilePath, 'Config lock file does not exists: ' . $output);
        File\assert_content($configPath, $initialContentWithPackagesDirectory, 'Config file content is not correct after running init!');
    },
    after: function () {
        flush($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject');
    }
);
