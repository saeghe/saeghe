<?php

namespace Tests\RemoveCommandTest;

test(
    title: 'it should remove a package',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=remove --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/complex-package.git");

        assert_desired_data_in_packages_directory('Package has not been deleted from Packages directory!' . $output);
        assert_config_file_is_clean('Packages has not been deleted from config file!' . $output);
        assert_meta_is_clean('Packages has not been deleted from meta!' . $output);
    },
    before: function () {
        shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/complex-package.git");
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
