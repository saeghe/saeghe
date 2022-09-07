<?php

namespace Tests\AddCommandTest;

use Saeghe\TestRunner\Assertions\File;

test(
    title: 'it should add package to the project',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/simple-package.git");

        assertBuildCreatedForSimpleProject('Config file is not created!' . $output);
        assertSimplePackageAddedToBuildConfig('Simple Package does not added to config file properly! ' . $output);
        assertPackagesDirectoryCreatedForEmptyProject('Package directory does not created.' . $output);
        assertSimplePackageCloned('Simple package does not cloned!' . $output);
        assertBuildLockHasDesiredData('Data in the lock files is not what we want.' . $output);
    },
    before: function () {
        deleteEmptyProjectBuildJson();
        deleteEmptyProjectBuildLock();
        deleteEmptyProjectPackagesDirectory();
    },
    after: function () {
        deleteEmptyProjectPackagesDirectory();
        deleteEmptyProjectBuildJson();
        deleteEmptyProjectBuildLock();
    }
);

test(
    title: 'it should add package to the project without trailing .git',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/EmptyProject --package=git@github.com:saeghe/simple-package");

        assertSimplePackageCloned('Simple package does not cloned!' . $output);
    },
    before: function () {
        deleteEmptyProjectBuildJson();
        deleteEmptyProjectBuildLock();
        deleteEmptyProjectPackagesDirectory();
    },
    after: function () {
        deleteEmptyProjectPackagesDirectory();
        deleteEmptyProjectBuildJson();
        deleteEmptyProjectBuildLock();
    }
);

function deleteEmptyProjectBuildJson()
{
    shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json');
}

function deleteEmptyProjectBuildLock()
{
    shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build-lock.json');
}

function deleteEmptyProjectPackagesDirectory()
{
    shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages');
}

function assertBuildCreatedForSimpleProject($message)
{
    File\assertExists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json', $message);
}

function assertPackagesDirectoryCreatedForEmptyProject($message)
{
    File\assertExists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages', $message);
}

function assertSimplePackageCloned($message)
{
    assert(
        File\assertExists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/Saeghe/simple-package')
        && File\assertExists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/Saeghe/simple-package/build.json')
        && File\assertExists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/Saeghe/simple-package/README.md'),
        $message
    );
}

function assertSimplePackageAddedToBuildConfig($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json'), true, JSON_THROW_ON_ERROR);

    assert(
        assert(isset($config['packages']['git@github.com:saeghe/simple-package.git']))
        && assert('development' === $config['packages']['git@github.com:saeghe/simple-package.git']),
        $message
    );
}

function assertBuildLockHasDesiredData($message)
{
    $lock = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build-lock.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($lock['packages']['git@github.com:saeghe/simple-package.git'])
        && 'development' === $lock['packages']['git@github.com:saeghe/simple-package.git']['version']
        && 'saeghe' === $lock['packages']['git@github.com:saeghe/simple-package.git']['owner']
        && 'simple-package' === $lock['packages']['git@github.com:saeghe/simple-package.git']['repo']
        && '3db611bcd9fe6732e011f61bd36ca60ff42f4946' === $lock['packages']['git@github.com:saeghe/simple-package.git']['hash'],
        $message
    );
}
