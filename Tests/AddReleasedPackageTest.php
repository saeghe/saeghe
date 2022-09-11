<?php

namespace Tests\AddReleasedPackageTest;

use Saeghe\TestRunner\Assertions\File;

test(
    title: 'it should add released package to the project',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/released-package.git");

        assert_build_created_for_released_project('Config file is not created!' . $output);
        assert_released_package_added_to_build_config('Released Package does not added to config file properly! ' . $output);
        assert_packages_directory_created_for_empty_project('Package directory does not created.' . $output);
        assert_released_package_cloned('Released package does not cloned!' . $output);
        assert_build_lock_has_desired_data('Data in the lock files is not what we want.' . $output);
        assert_zip_file_deleted('Zip file has not been deleted.' . $output);
    },
    before: function () {
        delete_empty_project_build_json();
        delete_empty_project_build_lock();
        delete_empty_project_packages_directory();
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
    shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages');
}

function assert_build_created_for_released_project($message)
{
    File\assert_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json', $message);
}

function assert_packages_directory_created_for_empty_project($message)
{
    File\assert_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages', $message);
}

function assert_released_package_cloned($message)
{
    assert(
        file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/build.json')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/build-lock.json')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/specific-to-v1.0.2.txt')
        && ! file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/Tests'),
        $message
    );
}

function assert_released_package_added_to_build_config($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($config['packages']['git@github.com:saeghe/released-package.git'])
        && 'v1.0.2' === $config['packages']['git@github.com:saeghe/released-package.git'],
        $message
    );
}

function assert_build_lock_has_desired_data($message)
{
    $lock = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build-lock.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($lock['packages']['git@github.com:saeghe/released-package.git'])
        && 'v1.0.2' === $lock['packages']['git@github.com:saeghe/released-package.git']['version']
        && 'saeghe' === $lock['packages']['git@github.com:saeghe/released-package.git']['owner']
        && 'released-package' === $lock['packages']['git@github.com:saeghe/released-package.git']['repo']
        && '9554ff78c29bf1d2b75940a22a0726f2fd953b43' === $lock['packages']['git@github.com:saeghe/released-package.git']['hash'],
        $message
    );
}

function assert_zip_file_deleted($message)
{
    assert(
        ! file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package.zip'),
        $message
    );
}
