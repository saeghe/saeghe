<?php

namespace Tests\UpdateCommandTest;

test(
    title: 'it should update to the latest version',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe update git@github.com:saeghe/released-package.git --project=TestRequirements/Fixtures/EmptyProject");

        assert_version_upgraded_in_config_file($output);
        assert_meta_updated($output);
    },
    before: function () {
        shell_exec($_SERVER['PWD'] . "/saeghe add git@github.com:saeghe/released-package.git --version=v1.0.3 --project=TestRequirements/Fixtures/EmptyProject");
    },
    after: function () {
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/*');
    }
);

test(
    title: 'it should update to the given version',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe update git@github.com:saeghe/released-package.git --version=v1.0.3 --project=TestRequirements/Fixtures/EmptyProject");

        assert_given_version_added('Package did not updated to given package version. ' . $output);
    },
    before: function () {
        shell_exec($_SERVER['PWD'] . "/saeghe add git@github.com:saeghe/released-package.git --version=v1.0.2 --project=TestRequirements/Fixtures/EmptyProject");
    },
    after: function () {
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/*');
    }
);

function assert_version_upgraded_in_config_file($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($config['packages']['git@github.com:saeghe/released-package.git'])
        && 'v1.0.5' === $config['packages']['git@github.com:saeghe/released-package.git'],
        $message
    );
}

function assert_meta_updated($message)
{
    $meta = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($meta['packages']['git@github.com:saeghe/released-package.git'])
        && 'v1.0.5' === $meta['packages']['git@github.com:saeghe/released-package.git']['version']
        && 'saeghe' === $meta['packages']['git@github.com:saeghe/released-package.git']['owner']
        && 'released-package' === $meta['packages']['git@github.com:saeghe/released-package.git']['repo']
        && '5885e5f' === $meta['packages']['git@github.com:saeghe/released-package.git']['hash'],
        $message
    );
}

function assert_given_version_added($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config.json'), true, JSON_THROW_ON_ERROR);
    $meta = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($config['packages']['git@github.com:saeghe/released-package.git'])
        && 'v1.0.3' === $config['packages']['git@github.com:saeghe/released-package.git']
        && isset($meta['packages']['git@github.com:saeghe/released-package.git'])
        && 'v1.0.3' === $meta['packages']['git@github.com:saeghe/released-package.git']['version']
        && 'saeghe' === $meta['packages']['git@github.com:saeghe/released-package.git']['owner']
        && 'released-package' === $meta['packages']['git@github.com:saeghe/released-package.git']['repo']
        && '9e9b796' === $meta['packages']['git@github.com:saeghe/released-package.git']['hash'],
        $message
    );
}
