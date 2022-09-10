<?php

namespace Tests\AddingComplexProjectTest;

test(
    title: 'it should add a complex project',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/ProjectWithTests --package=git@github.com:saeghe/complex-package.git");

        assertPacakgesAddedToPackagesDirectory('Packages does not added to the packages directory!' . $output);
        assertBuildFileHasDesiredData('Build file for adding complex package does not have desired data!' . $output);
        assertLockFileHasDesiredData('Build lock for adding complex package does not have desired data!' . $output);
    },
    before: function () {
        deleteBuildJson();
        deleteBuildLock();
        deletePackagesDirectory();
        copy($_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests/build.json', $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/build.json');
    },
    after: function () {
        deleteBuildJson();
        deleteBuildLock();
        deletePackagesDirectory();
    }
);

test(
    title: 'it should add a complex project with http path',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/ProjectWithTests --package=https://github.com/saeghe/complex-package.git");

        assertPacakgesAddedToPackagesDirectory('Packages does not added to the packages directory!' . $output);
    },
    before: function () {
        deleteBuildJson();
        deleteBuildLock();
        deletePackagesDirectory();
        copy($_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests/build.json', $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/build.json');
    },
    after: function () {
        deleteBuildJson();
        deleteBuildLock();
        deletePackagesDirectory();
    }
);

function deleteBuildJson()
{
    shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/build.json');
}

function deleteBuildLock()
{
    shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/build-lock.json');
}

function deletePackagesDirectory()
{
    shell_exec('rm -Rf ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Packages');
}

function assertPacakgesAddedToPackagesDirectory($message)
{
    assert(
        file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Packages/Saeghe/simple-package')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Packages/Saeghe/simple-package/build.json')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Packages/Saeghe/simple-package/README.md')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Packages/Saeghe/complex-package')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Packages/Saeghe/complex-package/build.json')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Packages/Saeghe/complex-package/build-lock.json'),
        $message
    );
}

function assertBuildFileHasDesiredData($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/build.json'), true, JSON_THROW_ON_ERROR);

    assert(
        assert(! isset($config['packages']['git@github.com:saeghe/simple-package.git']))

        && assert(isset($config['packages']['git@github.com:saeghe/complex-package.git']))
        && assert('development' === $config['packages']['git@github.com:saeghe/complex-package.git']),
        $message
    );
}

function assertLockFileHasDesiredData($message)
{
    $lock = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/build-lock.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($lock['packages']['git@github.com:saeghe/simple-package.git'])
        &&'development' === $lock['packages']['git@github.com:saeghe/simple-package.git']['version']
        &&'saeghe' === $lock['packages']['git@github.com:saeghe/simple-package.git']['owner']
        &&'simple-package' === $lock['packages']['git@github.com:saeghe/simple-package.git']['repo']
        && '3db611bcd9fe6732e011f61bd36ca60ff42f4946' === $lock['packages']['git@github.com:saeghe/simple-package.git']['hash']

        && isset($lock['packages']['git@github.com:saeghe/complex-package.git'])
        &&'development' === $lock['packages']['git@github.com:saeghe/complex-package.git']['version']
        && 'saeghe' === $lock['packages']['git@github.com:saeghe/complex-package.git']['owner']
        && 'complex-package' === $lock['packages']['git@github.com:saeghe/complex-package.git']['repo']
        && '5e60733132ddf50df675b2491e35b7bb01674c3e' === $lock['packages']['git@github.com:saeghe/complex-package.git']['hash'],
        $message
    );
}
