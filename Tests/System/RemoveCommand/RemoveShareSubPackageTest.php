<?php

namespace Tests\System\RemoveCommand\RemoveShareSubPackageTest;

use Saeghe\FileManager\FileType\Json;
use function Saeghe\FileManager\Directory\clean;
use function Saeghe\FileManager\Resolver\realpath;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should not delete package used in another package as sub package',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe remove git@github.com:saeghe/test-runner.git --project=TestRequirements/Fixtures/EmptyProject');

        assert_desired_data_in_packages_directory('Package has not been deleted from Packages directory!' . $output);
        assert_config_file_is_clean('Packages has not been deleted from config file!' . $output);
        assert_meta_is_clean('Packages has been deleted from meta file!' . $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/test-runner.git --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/cli.git --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);

function assert_desired_data_in_packages_directory($message)
{
    clearstatcache();
    assert_true((
        file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/cli'))
        && file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/test-runner'))
    ),
        $message
    );
}

function assert_config_file_is_clean($message)
{
    $config = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json'));

    assert_true((
        isset($config['packages']['git@github.com:saeghe/cli.git'])
        && ! isset($config['packages']['git@github.com:saeghe/test-runner.git'])
    ),
        $message
    );
}

function assert_meta_is_clean($message)
{
    $config = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json'));

    assert_true(isset($config['packages']['https://github.com/saeghe/cli.git']), $message . ' Cli package not found in the lock file.');
    assert_true(isset($config['packages']['git@github.com:saeghe/test-runner.git']), $message . ' Test runner package not found in the lock file.');
}
