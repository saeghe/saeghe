<?php

namespace Tests\System\AddComand\AddReleasedPackageTest;

use Saeghe\FileManager\FileType\Json;
use Saeghe\TestRunner\Assertions\File;
use function Saeghe\FileManager\Directory\clean;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\FileManager\Resolver\realpath;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;
use function Saeghe\TestRunner\Assertions\Boolean\assert_false;

test(
    title: 'it should add released package to the project',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/released-package.git --project=TestRequirements/Fixtures/EmptyProject');

        assert_config_file_created_for_released_project('Config file is not created!' . $output);
        assert_released_package_added_to_config('Released Package does not added to config file properly! ' . $output);
        assert_packages_directory_created_for_empty_project('Package directory does not created.' . $output);
        assert_released_package_cloned('Released package does not cloned!' . $output);
        assert_meta_has_desired_data('Meta data is not what we want.' . $output);
        assert_zip_file_deleted('Zip file has not been deleted.' . $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);

test(
    title: 'it should add development version of released package to the project if version passed as development',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/released-package.git --version=development --project=TestRequirements/Fixtures/EmptyProject');

        assert_development_branch_added('We expected to see development branch for the package! ' . $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);

function assert_development_branch_added($message)
{
    $meta = Json\to_array(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json');

    assert_true((
            isset($meta['packages']['git@github.com:saeghe/released-package.git'])
            && 'development' === $meta['packages']['git@github.com:saeghe/released-package.git']['version']
            && 'saeghe' === $meta['packages']['git@github.com:saeghe/released-package.git']['owner']
            && 'released-package' === $meta['packages']['git@github.com:saeghe/released-package.git']['repo']
            && file_exists(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/Tests')
        ),
        $message
    );
}

function assert_config_file_created_for_released_project($message)
{
    File\assert_file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json'), $message);
}

function assert_packages_directory_created_for_empty_project($message)
{
    File\assert_file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages'), $message);
}

function assert_released_package_cloned($message)
{
    assert_true((
            file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package'))
            && file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/saeghe.config.json'))
            && file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/saeghe.config-lock.json'))
            && file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/specific-to-v1.0.2.txt'))
            && ! file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/Tests'))
        ),
        $message
    );
}

function assert_released_package_added_to_config($message)
{
    $config = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json'));

    assert_true((
            isset($config['packages']['git@github.com:saeghe/released-package.git'])
            && 'v1.0.6' === $config['packages']['git@github.com:saeghe/released-package.git']
        ),
        $message
    );
}

function assert_meta_has_desired_data($message)
{
    $meta = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json'));

    assert_true((
            isset($meta['packages']['git@github.com:saeghe/released-package.git'])
            && 'v1.0.6' === $meta['packages']['git@github.com:saeghe/released-package.git']['version']
            && 'saeghe' === $meta['packages']['git@github.com:saeghe/released-package.git']['owner']
            && 'released-package' === $meta['packages']['git@github.com:saeghe/released-package.git']['repo']
            && '5885e5f3ed26c2289ceb2eeea1f108f7fbc10c01' === $meta['packages']['git@github.com:saeghe/released-package.git']['hash']
        ),
        $message
    );
}

function assert_zip_file_deleted($message)
{
    assert_false(file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package.zip')),
        $message
    );
}
