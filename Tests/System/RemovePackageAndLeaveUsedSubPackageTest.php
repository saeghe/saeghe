<?php

namespace Tests\System\RemovePackageAndLeaveUsedSubPackageTest;

use function Saeghe\Saeghe\FileManager\Directory\flush;
use function Saeghe\Saeghe\FileManager\Path\realpath;

test(
    title: 'it should remove the package but leave used subpackage',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe remove git@github.com:saeghe/complex-package.git --project=TestRequirements/Fixtures/EmptyProject");

        assert_desired_data_in_packages_directory('Package has not been deleted from Packages directory!' . $output);
        assert_config_file_is_clean('Packages has not been deleted from config file!' . $output);
        assert_meta_is_clean('Packages has not been deleted from meta file!' . $output);
    },
    before: function () {
        shell_exec($_SERVER['PWD'] . "/saeghe init --project=TestRequirements/Fixtures/EmptyProject");
        shell_exec($_SERVER['PWD'] . "/saeghe add git@github.com:saeghe/simple-package.git --project=TestRequirements/Fixtures/EmptyProject");
        shell_exec($_SERVER['PWD'] . "/saeghe add git@github.com:saeghe/complex-package.git --project=TestRequirements/Fixtures/EmptyProject");
    },
    after: function () {
        shell_exec($_SERVER['PWD'] . "/saeghe remove git@github.com:saeghe/simple-package.git --project=TestRequirements/Fixtures/EmptyProject");
        flush(realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject'));
    }
);

function assert_desired_data_in_packages_directory($message)
{
    clearstatcache();
    assert(file_exists(realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/simple-package'))
        && ! file_exists(realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/complex-package'))
    ,
        $message
    );
}

function assert_config_file_is_clean($message)
{
    $config = json_decode(file_get_contents(realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config.json')), true, JSON_THROW_ON_ERROR);

    assert(
        isset($config['packages']['git@github.com:saeghe/simple-package.git'])
        && ! isset($config['packages']['git@github.com:saeghe/complex-package.git']),
        $message
    );
}

function assert_meta_is_clean($message)
{
    $config = json_decode(file_get_contents(realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json')), true, JSON_THROW_ON_ERROR);

    assert(isset($config['packages']['git@github.com:saeghe/simple-package.git']), $message);
}
