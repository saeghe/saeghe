<?php

namespace Tests\BuildWithCustomPackageDirectoryTest;

test(
    title: 'it should build the project with custom packages directory',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . '/saeghe --command=build --project=TestRequirements/Fixtures/ProjectWithTests');

        assert_file_with_package_dependency_has_been_built('File with package dependency has not been built properly!' . $output);
    },
    before: function () {
        delete_build_directory();
        delete_packages_directory();
        copy($_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests/build-with-custom-packages-directory.json', $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/saeghe.config.json');
        shell_exec("{$_SERVER['PWD']}/saeghe --command=init --project=TestRequirements/Fixtures/ProjectWithTests --packages-directory=vendor");
        shell_exec($_SERVER['PWD'] . '/saeghe --command=add --project=TestRequirements/Fixtures/ProjectWithTests --package=git@github.com:saeghe/simple-package.git');
    },
    after: function () {
        delete_build_directory();
        delete_packages_directory();
        shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/saeghe.config.json');
        shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/saeghe.config-lock.json');
    }
);

function delete_build_directory()
{
    shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds');
}

function delete_packages_directory()
{
    shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/vendor');
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
