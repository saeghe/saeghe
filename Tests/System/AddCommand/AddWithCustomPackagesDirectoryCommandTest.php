<?php

namespace Tests\System\AddCommand\AddWithCustomPackagesDirectoryCommandTest;

use Saeghe\FileManager\FileType\Json;
use Saeghe\TestRunner\Assertions\File;
use function Saeghe\FileManager\Directory\clean;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\FileManager\Resolver\realpath;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should add package to the given directory',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/simple-package.git --project=TestRequirements/Fixtures/EmptyProject');

        assert_package_directory_added_to_config('Config does not contains the custom package directory!');
        assert_config_file_created_for_simple_project('Config file is not created!' . $output);
        assert_simple_package_added_to_config('Simple Package does not added to config file properly! ' . $output);
        assert_packages_directory_created_for_empty_project('Package directory does not created.' . $output);
        assert_simple_package_cloned('Simple package does not cloned!' . $output);
        assert_meta_has_desired_data('Meta data is not what we want.' . $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject --packages-directory=vendor');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);

function assert_package_directory_added_to_config($message)
{
    $config = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json'));

    assert_true(
        $config['packages-directory'] === 'vendor',
        $message
    );
}

function assert_config_file_created_for_simple_project($message)
{
    File\assert_file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json'), $message);
}

function assert_packages_directory_created_for_empty_project($message)
{
    File\assert_file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/vendor'), $message);
}

function assert_simple_package_cloned($message)
{
    assert_true((
            File\assert_file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/vendor/saeghe/simple-package'))
            && File\assert_file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/vendor/saeghe/simple-package/saeghe.config.json'))
            && File\assert_file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/vendor/saeghe/simple-package/README.md'))
        ),
        $message
    );
}

function assert_simple_package_added_to_config($message)
{
    $config = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json'));

    assert_true((
            isset($config['packages']['git@github.com:saeghe/simple-package.git'])
            && 'development' === $config['packages']['git@github.com:saeghe/simple-package.git']
        ),
        $message
    );
}

function assert_meta_has_desired_data($message)
{
    $meta = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json'));

    assert_true((
            isset($meta['packages']['git@github.com:saeghe/simple-package.git'])
            && 'development' === $meta['packages']['git@github.com:saeghe/simple-package.git']['version']
            && 'saeghe' === $meta['packages']['git@github.com:saeghe/simple-package.git']['owner']
            && 'simple-package' === $meta['packages']['git@github.com:saeghe/simple-package.git']['repo']
            && '85f94d8c34cb5678a5b37707479517654645c102' === $meta['packages']['git@github.com:saeghe/simple-package.git']['hash']
        ),
        $message
    );
}
