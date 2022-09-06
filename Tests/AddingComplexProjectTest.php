<?php

namespace Tests\AddingComplexProjectTest;

test(
    title: 'it should add a complex project',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/ProjectWithTests --path=git@github.com:saeghe/complex-package.git");

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

function deleteBuildJson()
{
    shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/build.json');
}

function deleteBuildLock()
{
    shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/build.lock');
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
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Packages/Saeghe/complex-package/build.lock'),
        $message
    );
}

function assertBuildFileHasDesiredData($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/build.json'), true, JSON_THROW_ON_ERROR);

    assert(
        assert(isset($config['packages']['Saeghe\SimplePackage']))
        && assert('git@github.com:saeghe/simple-package.git' === $config['packages']['Saeghe\SimplePackage']['path'])
        && assert('development' === $config['packages']['Saeghe\SimplePackage']['version'])

        && assert(isset($config['packages']['Saeghe\ComplexPackage']))
        && assert('git@github.com:saeghe/complex-package.git' === $config['packages']['Saeghe\ComplexPackage']['path'])
        && assert('development' === $config['packages']['Saeghe\ComplexPackage']['version']),
        $message
    );
}

function assertLockFileHasDesiredData($message)
{
    $lock = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/build.lock'), true, JSON_THROW_ON_ERROR);

    assert(
        'git@github.com:saeghe/simple-package.git' === $lock['Saeghe\SimplePackage']['path']
        &&'development' === $lock['Saeghe\SimplePackage']['version']
        &&'saeghe' === $lock['Saeghe\SimplePackage']['owner']
        &&'simple-package' === $lock['Saeghe\SimplePackage']['repo']
        && '3db611bcd9fe6732e011f61bd36ca60ff42f4946' === $lock['Saeghe\SimplePackage']['hash']

        && 'git@github.com:saeghe/complex-package.git' === $lock['Saeghe\ComplexPackage']['path']
        &&'development' === $lock['Saeghe\ComplexPackage']['version']
        && 'saeghe' === $lock['Saeghe\ComplexPackage']['owner']
        && 'complex-package' === $lock['Saeghe\ComplexPackage']['repo']
        && 'cbbe25a4748e7e3332293963586e2316e8a93def' === $lock['Saeghe\ComplexPackage']['hash'],
        $message
    );
}
