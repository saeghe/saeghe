<?php

namespace Tests\InstallCommand;

test(
    title: 'it should install packages from lock file',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=install --project=TestRequirements/Fixtures/EmptyProject");

        assertBuildContentNotChanged('Config file has been changed!' . $output);
        assertLockFileContentNotChanged('Released Package does not added to config file properly! ' . $output);
        assertPackageExistsInPackagesDirectory('Package does not exist in the packages directory.' . $output);
        assertZipFileDeleted('Zip file has not been deleted.' . $output);
    },
    before: function () {
        shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/released-package.git --version=v1.0.0");
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages');
    },
    after: function () {
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/*');
    },
);

function assertBuildContentNotChanged($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($config['packages']['git@github.com:saeghe/released-package.git'])
        && 'v1.0.0' === $config['packages']['git@github.com:saeghe/released-package.git'],
        $message
    );
}

function assertLockFileContentNotChanged($message)
{
    $lock = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build-lock.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($lock['packages']['git@github.com:saeghe/released-package.git'])
        && 'v1.0.0' === $lock['packages']['git@github.com:saeghe/released-package.git']['version']
        && 'saeghe' === $lock['packages']['git@github.com:saeghe/released-package.git']['owner']
        && 'released-package' === $lock['packages']['git@github.com:saeghe/released-package.git']['repo']
        && 'ae5c24f584ff6c7112162aa88fa02b0e14f5f125' === $lock['packages']['git@github.com:saeghe/released-package.git']['hash'],
        $message
    );
}

function assertPackageExistsInPackagesDirectory($message)
{
    assert(
        file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/build.json')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/build-lock.json')
        && ! file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/Tests'),
        $message
    );
}

function assertZipFileDeleted($message)
{
    assert(
        ! file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package.zip'),
        $message
    );
}
