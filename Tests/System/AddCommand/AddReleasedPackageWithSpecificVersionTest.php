<?php

namespace Tests\System\AddCommand\AddReleasedPackageWithSpecificVersionTest;

use Saeghe\TestRunner\Assertions\File;
use function Saeghe\FileManager\Directory\flush;

test(
    title: 'it should add released package to the project with the given version',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe add git@github.com:saeghe/released-package.git --version=v1.0.3 --project=TestRequirements/Fixtures/EmptyProject");

        assert_config_file_created_for_released_project('Config file is not created!' . $output);
        assert_released_package_added_to_config('Released Package does not added to config file properly! ' . $output);
        assert_packages_directory_created_for_empty_project('Package directory does not created.' . $output);
        assert_released_package_cloned('Released package does not cloned!' . $output);
        assert_meta_has_desired_data('Meta data is not what we want.' . $output);
    },
    before: function () {
        shell_exec($_SERVER['PWD'] . "/saeghe init --project=TestRequirements/Fixtures/EmptyProject");
    },
    after: function () {
        flush($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject');
    }
);

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
        && ! file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/Tests'),
        $message
    );
}

function assert_released_package_added_to_config($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($config['packages']['git@github.com:saeghe/released-package.git'])
        && 'v1.0.3' === $config['packages']['git@github.com:saeghe/released-package.git'],
        $message
    );
}

function assert_meta_has_desired_data($message)
{
    $meta = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($meta['packages']['git@github.com:saeghe/released-package.git'])
        && 'v1.0.3' === $meta['packages']['git@github.com:saeghe/released-package.git']['version']
        && 'saeghe' === $meta['packages']['git@github.com:saeghe/released-package.git']['owner']
        && 'released-package' === $meta['packages']['git@github.com:saeghe/released-package.git']['repo']
        && '9e9b796915596f7c5e0b91d2f9fa5f916a9b5cc8' === $meta['packages']['git@github.com:saeghe/released-package.git']['hash'],
        $message
    );
}
