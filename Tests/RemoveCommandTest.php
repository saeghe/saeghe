<?php

namespace Tests\RemoveCommandTest;

use function Saeghe\Cli\IO\Write\assert_error;
use function Saeghe\Cli\IO\Write\assert_success;

test(
    title: 'it should remove a package',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe remove git@github.com:saeghe/complex-package.git --project=TestRequirements/Fixtures/EmptyProject");

        assert_success('Package git@github.com:saeghe/complex-package.git has been removed successfully.', $output);
        assert_desired_data_in_packages_directory('Package has not been deleted from Packages directory!' . $output);
        assert_config_file_is_clean('Packages has not been deleted from config file!' . $output);
        assert_meta_is_clean('Packages has not been deleted from meta!' . $output);

        $output = shell_exec($_SERVER['PWD'] . "/saeghe remove git@github.com:saeghe/complex-package.git --project=TestRequirements/Fixtures/EmptyProject");

        assert_error("Package git@github.com:saeghe/complex-package.git does not found in your project!", $output);
    },
    before: function () {
        shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config.json');
        shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json');
        shell_exec($_SERVER['PWD'] . "/saeghe init --project=TestRequirements/Fixtures/EmptyProject");
        shell_exec($_SERVER['PWD'] . "/saeghe add git@github.com:saeghe/complex-package.git --project=TestRequirements/Fixtures/EmptyProject");
    },
    after: function () {
        shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config.json');
        shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json');
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages');
    }
);

function assert_desired_data_in_packages_directory($message)
{
    clearstatcache();
    assert(! file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/simple-package')
        && ! file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/complex-package')
    ,
        $message
    );
}

function assert_config_file_is_clean($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config.json'), true, JSON_THROW_ON_ERROR);

    assert($config['packages'] === [], $message);
}

function assert_meta_is_clean($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json'), true, JSON_THROW_ON_ERROR);

    assert($config['packages'] === [], $message);
}
