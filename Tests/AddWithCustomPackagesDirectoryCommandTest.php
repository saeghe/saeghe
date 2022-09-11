<?php

namespace Tests\AddWithCustomPackagesDirectoryCommandTest;

use Saeghe\TestRunner\Assertions\File;

test(
    title: 'it should add package to the given directory',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/simple-package.git");

        assert_package_directory_added_to_config('Config does not contains the custom package directory!');
        assert_build_created_for_simple_project('Config file is not created!' . $output);
        assert_simple_package_added_to_build_config('Simple Package does not added to config file properly! ' . $output);
        assert_packages_directory_created_for_empty_project('Package directory does not created.' . $output);
        assert_simple_package_cloned('Simple package does not cloned!' . $output);
        assert_build_lock_has_desired_data('Data in the lock files is not what we want.' . $output);
    },
    before: function () {
        delete_empty_project_build_json();
        delete_empty_project_build_lock();
        delete_empty_project_packages_directory();
        shell_exec("{$_SERVER['PWD']}/saeghe --command=initialize --project=TestRequirements/Fixtures/EmptyProject --packages-directory=vendor");
    },
    after: function () {
        delete_empty_project_packages_directory();
        delete_empty_project_build_json();
        delete_empty_project_build_lock();
    }
);

function delete_empty_project_build_json()
{
    shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json');
}

function delete_empty_project_build_lock()
{
    shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build-lock.json');
}

function delete_empty_project_packages_directory()
{
    shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/vendor');
}

function assert_package_directory_added_to_config($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json'), true, JSON_THROW_ON_ERROR);

    assert(
        $config['packages-directory'] === 'vendor',
        $message
    );
}

function assert_build_created_for_simple_project($message)
{
    File\assert_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json', $message);
}

function assert_packages_directory_created_for_empty_project($message)
{
    File\assert_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/vendor', $message);
}

function assert_simple_package_cloned($message)
{
    assert(
        File\assert_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/vendor/Saeghe/simple-package')
        && File\assert_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/vendor/Saeghe/simple-package/build.json')
        && File\assert_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/vendor/Saeghe/simple-package/README.md'),
        $message
    );
}

function assert_simple_package_added_to_build_config($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json'), true, JSON_THROW_ON_ERROR);

    assert(
        assert(isset($config['packages']['git@github.com:saeghe/simple-package.git']))
        && assert('development' === $config['packages']['git@github.com:saeghe/simple-package.git']),
        $message
    );
}

function assert_build_lock_has_desired_data($message)
{
    $lock = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build-lock.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($lock['packages']['git@github.com:saeghe/simple-package.git'])
        && 'development' === $lock['packages']['git@github.com:saeghe/simple-package.git']['version']
        && 'saeghe' === $lock['packages']['git@github.com:saeghe/simple-package.git']['owner']
        && 'simple-package' === $lock['packages']['git@github.com:saeghe/simple-package.git']['repo']
        && '3db611bcd9fe6732e011f61bd36ca60ff42f4946' === $lock['packages']['git@github.com:saeghe/simple-package.git']['hash'],
        $message
    );
}
