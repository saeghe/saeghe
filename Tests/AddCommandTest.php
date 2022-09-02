<?php

use Saeghe\TestRunner\Assertions\File;

test(
    title: 'it should add package to the project',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=Tests/Fixtures/EmptyProject --path=git@github.com:saeghe/simple-package.git");

        assertBuildCreatedForSimpleProject('Config file is not created!' . $output);
        assertSimplePackageAddedToBuildConfig('Simple Package does not added to config file properly! ' . $output);
        assertPackagesDirectoryCreatedForEmptyProject('Package directory does not created.' . $output);
        assertSimplePackageCloned('Simple package does not cloned!' . $output);

    },
    before: function () {
        deleteEmptyProjectBuildFile();
        deleteEmptyProjectPackagesDirectory();
    },
    after: function () {
        deleteEmptyProjectPackagesDirectory();
        deleteEmptyProjectBuildFile();
    }
);

function deleteEmptyProjectBuildFile()
{
    shell_exec('rm -f ' . $_SERVER['PWD'] . '/Tests/Fixtures/EmptyProject/build.json');
}

function deleteEmptyProjectPackagesDirectory()
{
    shell_exec('rm -fR ' . $_SERVER['PWD'] . '/Tests/Fixtures/EmptyProject/Packages');
}

function assertBuildCreatedForSimpleProject($message)
{
    File\assertExists($_SERVER['PWD'] . '/Tests/Fixtures/EmptyProject/build.json', $message);
}

function assertPackagesDirectoryCreatedForEmptyProject($message)
{
    File\assertExists($_SERVER['PWD'] . '/Tests/Fixtures/EmptyProject/Packages', $message);
}

function assertSimplePackageCloned($message)
{
    assert(
        File\assertExists($_SERVER['PWD'] . '/Tests/Fixtures/EmptyProject/Packages/Saeghe/simple-package')
        && File\assertExists($_SERVER['PWD'] . '/Tests/Fixtures/EmptyProject/Packages/Saeghe/simple-package/build.json')
        && File\assertExists($_SERVER['PWD'] . '/Tests/Fixtures/EmptyProject/Packages/Saeghe/simple-package/README.md'),
        $message
    );
}

function assertSimplePackageAddedToBuildConfig($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/Tests/Fixtures/EmptyProject/build.json'), true, JSON_THROW_ON_ERROR);

    assert(
        assert(isset($config['packages']['Saeghe\SimplePackage']))
        && assert('git@github.com:saeghe/simple-package.git' === $config['packages']['Saeghe\SimplePackage']['path'])
        && assert('dev-master' === $config['packages']['Saeghe\SimplePackage']['version']),
        $message
    );
}

