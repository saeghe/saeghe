<?php

namespace Tests\System\UpdateCommandTest;

use function Saeghe\Cli\IO\Write\assert_error;
use function Saeghe\Cli\IO\Write\assert_success;
use function Saeghe\Saeghe\FileManager\Directory\flush;
use function Saeghe\Saeghe\FileManager\Path\realpath;

test(
    title: 'it should update to the latest version',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe update git@github.com:saeghe/released-package.git --project=TestRequirements/Fixtures/EmptyProject');

        assert_success('Package git@github.com:saeghe/released-package.git has been updated.', $output);
        assert_version_upgraded_in_config_file($output);
        assert_meta_updated($output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/released-package.git --version=v1.0.3 --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        flush(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);

test(
    title: 'it should show error message when package does not exists',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe update git@github.com:saeghe/released-package.git --project=TestRequirements/Fixtures/EmptyProject');

        assert_error('Package git@github.com:saeghe/released-package.git does not found in your project!', $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/simple-package.git --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        flush(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);

test(
    title: 'it should update to the given version',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe update git@github.com:saeghe/released-package.git --version=v1.0.3 --project=TestRequirements/Fixtures/EmptyProject');

        assert_given_version_added('Package did not updated to given package version. ' . $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/released-package.git --version=v1.0.2 --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        flush(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);

function assert_version_upgraded_in_config_file($message)
{
    $config = json_decode(file_get_contents(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json')), true, JSON_THROW_ON_ERROR);

    assert(
        isset($config['packages']['git@github.com:saeghe/released-package.git'])
        && 'v1.0.5' === $config['packages']['git@github.com:saeghe/released-package.git'],
        $message
    );
}

function assert_meta_updated($message)
{
    $meta = json_decode(file_get_contents(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json')), true, JSON_THROW_ON_ERROR);

    assert(
        isset($meta['packages']['git@github.com:saeghe/released-package.git'])
        && 'v1.0.5' === $meta['packages']['git@github.com:saeghe/released-package.git']['version']
        && 'saeghe' === $meta['packages']['git@github.com:saeghe/released-package.git']['owner']
        && 'released-package' === $meta['packages']['git@github.com:saeghe/released-package.git']['repo']
        && '5885e5f3ed26c2289ceb2eeea1f108f7fbc10c01' === $meta['packages']['git@github.com:saeghe/released-package.git']['hash'],
        $message
    );
}

function assert_given_version_added($message)
{
    $config = json_decode(file_get_contents(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json')), true, JSON_THROW_ON_ERROR);
    $meta = json_decode(file_get_contents(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json')), true, JSON_THROW_ON_ERROR);

    assert(
        isset($config['packages']['git@github.com:saeghe/released-package.git'])
        && 'v1.0.3' === $config['packages']['git@github.com:saeghe/released-package.git']
        && isset($meta['packages']['git@github.com:saeghe/released-package.git'])
        && 'v1.0.3' === $meta['packages']['git@github.com:saeghe/released-package.git']['version']
        && 'saeghe' === $meta['packages']['git@github.com:saeghe/released-package.git']['owner']
        && 'released-package' === $meta['packages']['git@github.com:saeghe/released-package.git']['repo']
        && '9e9b796915596f7c5e0b91d2f9fa5f916a9b5cc8' === $meta['packages']['git@github.com:saeghe/released-package.git']['hash'],
        $message
    );
}
