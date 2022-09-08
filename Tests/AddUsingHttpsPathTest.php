<?php

namespace Tests\AddUsingHttpsPath;

use Saeghe\TestRunner\Assertions\File;

test(
    title: 'it should add package to the project',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/EmptyProject --package=https://github.com/symfony/thanks.git");

        assertBuildCreatedForHttpProject('Config file is not created!' . $output);
        assertHttpPackageAddedToBuildConfig('Http Package does not added to config file properly! ' . $output);
        assertPackagesDirectoryCreatedForEmptyProject('Package directory does not created.' . $output);
        assertHttpPackageCloned('Http package does not cloned!' . $output);
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
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/EmptyProject --package=https://github.com/symfony/thanks.git");

        assertHttpPackageCloned('Http package does not cloned!' . $output);
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

function assertBuildCreatedForHttpProject($message)
{
    File\assertExists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json', $message);
}

function assertPackagesDirectoryCreatedForEmptyProject($message)
{
    File\assertExists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages', $message);
}

function assertHttpPackageCloned($message)
{
    assert(
        File\assertExists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/symfony/thanks')
        && File\assertExists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/symfony/thanks/composer.json')
        && File\assertExists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/symfony/thanks/README.md'),
        $message
    );
}

function assertHttpPackageAddedToBuildConfig($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build.json'), true, JSON_THROW_ON_ERROR);

    assert(
        assert(isset($config['packages']['https://github.com/symfony/thanks.git']))
        && assert('v1.2.10' === $config['packages']['https://github.com/symfony/thanks.git']),
        $message
    );
}

function assertBuildLockHasDesiredData($message)
{
    $lock = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/build-lock.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($lock['packages']['https://github.com/symfony/thanks.git'])
        && 'v1.2.10' === $lock['packages']['https://github.com/symfony/thanks.git']['version']
        && 'symfony' === $lock['packages']['https://github.com/symfony/thanks.git']['owner']
        && 'thanks' === $lock['packages']['https://github.com/symfony/thanks.git']['repo']
        && 'e9c4709' === $lock['packages']['https://github.com/symfony/thanks.git']['hash'],
        $message
    );
}
