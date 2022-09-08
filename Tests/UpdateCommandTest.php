<?php

namespace Tests\UpdateCommandTest;

test(
    title: 'it should update to the latest version',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=update --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/released-package.git");

        assertVersionUpgradedInBuildFile($output);
        assertBuildLockUpdated($output);
    },
    before: function () {
        shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/released-package.git --version=v1.0.0");
    },
    after: function () {
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/*');
    }
);

function assertVersionUpgradedInBuildFile($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($config['packages']['git@github.com:saeghe/released-package.git'])
        && 'v1.0.2' === $config['packages']['git@github.com:saeghe/released-package.git'],
        $message
    );
}

function assertBuildLockUpdated($message)
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
