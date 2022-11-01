<?php

namespace Tests\System\InitCommandTest;

use function Saeghe\Cli\IO\Write\assert_success;
use Saeghe\TestRunner\Assertions\File;
use function Saeghe\Saeghe\FileManager\Directory\flush;
use function Saeghe\Saeghe\FileManager\Path\realpath;

$initial_content = <<<EOD
{
    "map": [],
    "entry-points": [],
    "excludes": [],
    "executables": [],
    "packages-directory": "Packages",
    "packages": []
}

EOD;

$initial_content_with_packages_directory = <<<EOD
{
    "map": [],
    "entry-points": [],
    "excludes": [],
    "executables": [],
    "packages-directory": "vendor",
    "packages": []
}

EOD;

$meta_content = <<<EOD
{
    "packages": []
}

EOD;


test(
    title: 'it makes a new default config file',
    case: function () use ($initial_content, $meta_content) {
        $packages_directory = root() . 'TestRequirements/Fixtures/EmptyProject/Packages';
        $config_path = root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json';
        $meta_file_path = root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json';

        $output = shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');

        File\assert_exists($config_path, 'Config file does not exists: ' . $output);
        File\assert_exists($packages_directory, 'Packages directory is not created: ' . $output);
        File\assert_content($config_path, $initial_content, 'Config file content is not correct after running init!');
        File\assert_content($meta_file_path, $meta_content, 'Lock file content is not correct after running init!');
        assert_success('Project has been initialized.', $output);
    },
    after: function () {
        flush(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);

test(
    title: 'it makes a new config file with given packages directory',
    case: function () use ($initial_content_with_packages_directory) {
        $config_path = root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json';
        $meta_file_path = root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json';
        $packages_directory = root() . 'TestRequirements/Fixtures/EmptyProject/vendor';

        $output = shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject --packages-directory=vendor');

        File\assert_exists($packages_directory, 'packages directory has not been created: ' . $output);
        File\assert_exists($config_path, 'Config file does not exists: ' . $output);
        File\assert_exists($meta_file_path, 'Config lock file does not exists: ' . $output);
        File\assert_content($config_path, $initial_content_with_packages_directory, 'Config file content is not correct after running init!');
    },
    after: function () {
        flush(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);
