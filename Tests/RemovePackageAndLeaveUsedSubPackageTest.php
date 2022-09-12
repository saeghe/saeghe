<?php

namespace Tests\RemovePackageAndLeaveUsedSubPackageTest;

test(
    title: 'it should remove the package but leave used subpackage',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=remove --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/complex-package.git");

        assert_desired_data_in_packages_directory('Package has not been deleted from Packages directory!' . $output);
        assert_config_file_is_clean('Packages has not been deleted from config file!' . $output);
        assert_meta_is_clean('Packages has not been deleted from meta file!' . $output);
    },
    before: function () {
        shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/simple-package.git");
        shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/complex-package.git");
    },
    after: function () {
        shell_exec($_SERVER['PWD'] . "/saeghe --command=remove --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/simple-package.git");
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/*');
    }
);

function assert_desired_data_in_packages_directory($message)
{
    clearstatcache();
    assert(file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/simple-package')
        && ! file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/complex-package')
    ,
        $message
    );
}

function assert_config_file_is_clean($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($config['packages']['git@github.com:saeghe/simple-package.git'])
        && ! isset($config['packages']['git@github.com:saeghe/complex-package.git']),
        $message
    );
}

function assert_meta_is_clean($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json'), true, JSON_THROW_ON_ERROR);

    assert(isset($config['packages']['git@github.com:saeghe/simple-package.git']), $message);
}