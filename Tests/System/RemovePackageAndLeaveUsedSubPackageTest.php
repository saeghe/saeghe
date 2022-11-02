<?php

namespace Tests\System\RemovePackageAndLeaveUsedSubPackageTest;

use Saeghe\Saeghe\FileManager\FileType\Json;
use function Saeghe\Saeghe\FileManager\Directory\flush;
use function Saeghe\Saeghe\FileManager\Path\realpath;

test(
    title: 'it should remove the package but leave used subpackage',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe remove git@github.com:saeghe/complex-package.git --project=TestRequirements/Fixtures/EmptyProject');

        assert_desired_data_in_packages_directory('Package has not been deleted from Packages directory!' . $output);
        assert_config_file_is_clean('Packages has not been deleted from config file!' . $output);
        assert_meta_is_clean('Packages has not been deleted from meta file!' . $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/simple-package.git --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/complex-package.git --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        shell_exec('php ' . root() . 'saeghe remove git@github.com:saeghe/simple-package.git --project=TestRequirements/Fixtures/EmptyProject');
        flush(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);

function assert_desired_data_in_packages_directory($message)
{
    clearstatcache();
    assert(file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/simple-package'))
        && ! file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/complex-package'))
    ,
        $message
    );
}

function assert_config_file_is_clean($message)
{
    $config = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json'));

    assert(
        isset($config['packages']['git@github.com:saeghe/simple-package.git'])
        && ! isset($config['packages']['git@github.com:saeghe/complex-package.git']),
        $message
    );
}

function assert_meta_is_clean($message)
{
    $config = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json'));

    assert(isset($config['packages']['git@github.com:saeghe/simple-package.git']), $message);
}
