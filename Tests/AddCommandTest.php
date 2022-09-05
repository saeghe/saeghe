<?php

namespace Tests\AddCommandTest;

use Saeghe\TestRunner\Assertions\File;

test(
    title: 'it should add package to the project',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/EmptyProject --path=git@github.com:saeghe/simple-package.git");

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

function deleteEmptyProjectBuildJson()
{
    shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json');
}

function deleteEmptyProjectBuildLock()
{
    shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.lock');
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
        assert(isset($config['packages']['Saeghe\SimplePackage']))
        && assert('git@github.com:saeghe/simple-package.git' === $config['packages']['Saeghe\SimplePackage']['path'])
        && assert('development' === $config['packages']['Saeghe\SimplePackage']['version']),
        $message
    );
}

function assertBuildLockHasDesiredData($message)
{
    $lock = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.lock'), true, JSON_THROW_ON_ERROR);

    assert(
        'git@github.com:saeghe/simple-package.git' === $lock['Saeghe\SimplePackage']['path']
        && 'development' === $lock['Saeghe\SimplePackage']['version']
        && 'saeghe' === $lock['Saeghe\SimplePackage']['owner']
        && 'simple-package' === $lock['Saeghe\SimplePackage']['repo']
        && 'bb176a6c76fb1248aa4d4844a78fd484a9c5b318' === $lock['Saeghe\SimplePackage']['hash'],
        $message
    );
}
