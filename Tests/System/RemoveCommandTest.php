<?php

namespace Tests\System\RemoveCommandTest;

use Saeghe\Saeghe\FileManager\FileType\Json;
use function Saeghe\Cli\IO\Write\assert_error;
use function Saeghe\Cli\IO\Write\assert_success;
use function Saeghe\Saeghe\FileManager\Directory\clean;
use function Saeghe\Saeghe\FileManager\Resolver\realpath;

test(
    title: 'it should remove a package',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe remove git@github.com:saeghe/complex-package.git --project=TestRequirements/Fixtures/EmptyProject');

        assert_success('Package git@github.com:saeghe/complex-package.git has been removed successfully.', $output);
        assert_desired_data_in_packages_directory('Package has not been deleted from Packages directory!' . $output);
        assert_config_file_is_clean('Packages has not been deleted from config file!' . $output);
        assert_meta_is_clean('Packages has not been deleted from meta!' . $output);

        $output = shell_exec('php ' . root() . 'saeghe remove git@github.com:saeghe/complex-package.git --project=TestRequirements/Fixtures/EmptyProject');

        assert_error("Package git@github.com:saeghe/complex-package.git does not found in your project!", $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/complex-package.git --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);

function assert_desired_data_in_packages_directory($message)
{
    clearstatcache();
    assert_true((
            ! file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/simple-package'))
            && ! file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/complex-package'))
        ),
        $message
    );
}

function assert_config_file_is_clean($message)
{
    $config = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json'));

    assert_true($config['packages'] === [], $message);
}

function assert_meta_is_clean($message)
{
    $config = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json'));

    assert_true($config['packages'] === [], $message);
}
