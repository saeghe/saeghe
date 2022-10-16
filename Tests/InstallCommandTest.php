<?php

namespace Tests\InstallCommand;

require_once __DIR__ . '/../Packages/saeghe/cli/Source/IO/Write.php';

use function Saeghe\Cli\IO\Write\assert_success;

test(
    title: 'it should install packages from lock file',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe install --project=TestRequirements/Fixtures/EmptyProject");

        assert_config_file_content_not_changed('Config file has been changed!' . $output);
        assert_meta_file_content_not_changed('Released Package meta data does not added to meta file properly! ' . $output);
        assert_package_exists_in_packages_directory('Package does not exist in the packages directory.' . $output);
        assert_zip_file_deleted('Zip file has not been deleted.' . $output);
        assert_success('Packages has been installed successfully.', $output);
    },
    before: function () {
        shell_exec($_SERVER['PWD'] . "/saeghe add git@github.com:saeghe/released-package.git --version=v1.0.3 --project=TestRequirements/Fixtures/EmptyProject");
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages');
    },
    after: function () {
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/*');
    },
);

function assert_config_file_content_not_changed($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($config['packages']['git@github.com:saeghe/released-package.git'])
        && 'v1.0.3' === $config['packages']['git@github.com:saeghe/released-package.git'],
        $message
    );
}

function assert_meta_file_content_not_changed($message)
{
    $meta = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($meta['packages']['git@github.com:saeghe/released-package.git'])
        && 'v1.0.3' === $meta['packages']['git@github.com:saeghe/released-package.git']['version']
        && 'saeghe' === $meta['packages']['git@github.com:saeghe/released-package.git']['owner']
        && 'released-package' === $meta['packages']['git@github.com:saeghe/released-package.git']['repo']
        && '9e9b796' === $meta['packages']['git@github.com:saeghe/released-package.git']['hash'],
        $message
    );
}

function assert_package_exists_in_packages_directory($message)
{
    assert(
        file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/saeghe.config.json')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/saeghe.config-lock.json')
        && ! file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/Tests'),
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
