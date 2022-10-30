<?php

namespace Tests\BuildCommand\BuildWithCustomPackageDirectoryTest;

use function Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\FileManager\File\delete;

test(
    title: 'it should build the project with custom packages directory',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . '/saeghe build --project=TestRequirements/Fixtures/ProjectWithTests');

        assert_file_with_package_dependency_has_been_built('File with package dependency has not been built properly!' . $output);
    },
    before: function () {
        copy($_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests/build-with-custom-packages-directory.json', $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/saeghe.config.json');
        shell_exec("{$_SERVER['PWD']}/saeghe init --project=TestRequirements/Fixtures/ProjectWithTests --packages-directory=vendor");
        shell_exec($_SERVER['PWD'] . '/saeghe add git@github.com:saeghe/simple-package.git --project=TestRequirements/Fixtures/ProjectWithTests');
    },
    after: function () {
        delete_build_directory();
        delete_packages_directory();
        delete($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/saeghe.config.json');
        delete($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/saeghe.config-lock.json');
    }
);

function delete_build_directory()
{
    delete_recursive($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds');
}

function delete_packages_directory()
{
    delete_recursive($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/vendor');
}

function assert_file_with_package_dependency_has_been_built($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development';
    $stubsDirectory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists($environmentBuildPath . '/Source/FileUsingVendor.php')
        && file_get_contents($environmentBuildPath . '/Source/FileUsingVendor.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/FileUsingVendor.stub')),
        $message
    );
}
