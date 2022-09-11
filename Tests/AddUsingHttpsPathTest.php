<?php

namespace Tests\AddUsingHttpsPath;

use Saeghe\TestRunner\Assertions\File;

test(
    title: 'it should add package to the project',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/EmptyProject --package=https://github.com/symfony/thanks.git");

        assert_build_created_for_http_project('Config file is not created!' . $output);
        assert_http_package_added_to_build_config('Http Package does not added to config file properly! ' . $output);
        assert_packages_directory_created_for_empty_project('Package directory does not created.' . $output);
        assert_http_package_cloned('Http package does not cloned!' . $output);
        assert_build_lock_has_desired_data('Data in the lock files is not what we want.' . $output);
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

test(
    title: 'it should add package to the project without trailing .git',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/EmptyProject --package=https://github.com/symfony/thanks.git");

        assert_http_package_cloned('Http package does not cloned!' . $output);
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

function assert_build_created_for_http_project($message)
{
    File\assert_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json', $message);
}

function assert_packages_directory_created_for_empty_project($message)
{
    File\assert_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages', $message);
}

function assert_http_package_cloned($message)
{
    assert(
        File\assert_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/symfony/thanks')
        && File\assert_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/symfony/thanks/composer.json')
        && File\assert_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/symfony/thanks/README.md'),
        $message
    );
}

function assert_http_package_added_to_build_config($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json'), true, JSON_THROW_ON_ERROR);

    assert(
        assert(isset($config['packages']['https://github.com/symfony/thanks.git']))
        && assert('v1.2.10' === $config['packages']['https://github.com/symfony/thanks.git']),
        $message
    );
}

function assert_build_lock_has_desired_data($message)
{
    $lock = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build-lock.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($lock['packages']['https://github.com/symfony/thanks.git'])
        && 'v1.2.10' === $lock['packages']['https://github.com/symfony/thanks.git']['version']
        && 'symfony' === $lock['packages']['https://github.com/symfony/thanks.git']['owner']
        && 'thanks' === $lock['packages']['https://github.com/symfony/thanks.git']['repo']
        && 'e9c4709' === $lock['packages']['https://github.com/symfony/thanks.git']['hash'],
        $message
    );
}
