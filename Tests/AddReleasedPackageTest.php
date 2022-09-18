<?php

namespace Tests\AddReleasedPackageTest;

use Saeghe\TestRunner\Assertions\File;

test(
    title: 'it should add released package to the project',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe add --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/released-package.git");

        assert_config_file_created_for_released_project('Config file is not created!' . $output);
        assert_released_package_added_to_config('Released Package does not added to config file properly! ' . $output);
        assert_packages_directory_created_for_empty_project('Package directory does not created.' . $output);
        assert_released_package_cloned('Released package does not cloned!' . $output);
        assert_meta_has_desired_data('Meta data is not what we want.' . $output);
        assert_zip_file_deleted('Zip file has not been deleted.' . $output);
    },
    before: function () {
        delete_empty_project_config_file();
        delete_empty_project_meta_file();
        delete_empty_project_packages_directory();
    },
    after: function () {
        delete_empty_project_packages_directory();
        delete_empty_project_config_file();
        delete_empty_project_meta_file();
    }
);

test(
    title: 'it should add development version of released package to the project if version passed as development',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe add --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/released-package.git --version=development");

        assert_development_branch_added('We expected to see development branch for the package! ' . $output);
    },
    before: function () {
        delete_empty_project_config_file();
        delete_empty_project_meta_file();
        delete_empty_project_packages_directory();
    },
    after: function () {
        delete_empty_project_packages_directory();
        delete_empty_project_config_file();
        delete_empty_project_meta_file();
    }
);

function assert_development_branch_added($message)
{
    $meta = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($meta['packages']['git@github.com:saeghe/released-package.git'])
        && 'development' === $meta['packages']['git@github.com:saeghe/released-package.git']['version']
        && 'saeghe' === $meta['packages']['git@github.com:saeghe/released-package.git']['owner']
        && 'released-package' === $meta['packages']['git@github.com:saeghe/released-package.git']['repo']
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/Tests'),
        $message
    );
}

function delete_empty_project_config_file()
{
    shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config.json');
}

function delete_empty_project_meta_file()
{
    shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json');
}

function delete_empty_project_packages_directory()
{
    shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages');
}

function assert_config_file_created_for_released_project($message)
{
    File\assert_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config.json', $message);
}

function assert_packages_directory_created_for_empty_project($message)
{
    File\assert_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages', $message);
}

function assert_released_package_cloned($message)
{
    assert(
        file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/saeghe.config.json')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/saeghe.config-lock.json')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/specific-to-v1.0.2.txt')
        && ! file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/Tests'),
        $message
    );
}

function assert_released_package_added_to_config($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($config['packages']['git@github.com:saeghe/released-package.git'])
        && 'v1.0.5' === $config['packages']['git@github.com:saeghe/released-package.git'],
        $message
    );
}

function assert_meta_has_desired_data($message)
{
    $meta = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($meta['packages']['git@github.com:saeghe/released-package.git'])
        && 'v1.0.5' === $meta['packages']['git@github.com:saeghe/released-package.git']['version']
        && 'saeghe' === $meta['packages']['git@github.com:saeghe/released-package.git']['owner']
        && 'released-package' === $meta['packages']['git@github.com:saeghe/released-package.git']['repo']
        && '5885e5f3ed26c2289ceb2eeea1f108f7fbc10c01' === $meta['packages']['git@github.com:saeghe/released-package.git']['hash'],
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
