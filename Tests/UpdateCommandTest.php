<?php

namespace Tests\UpdateCommandTest;

test(
    title: 'it should update to the latest version',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=update --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/released-package.git");

        assert_version_upgraded_in_config_file($output);
        assert_meta_updated($output);
    },
    before: function () {
        shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/released-package.git --version=v1.0.3");
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
        && '5885e5f3ed26c2289ceb2eeea1f108f7fbc10c01' === $meta['packages']['git@github.com:saeghe/released-package.git']['hash'],
        $message
    );
}
