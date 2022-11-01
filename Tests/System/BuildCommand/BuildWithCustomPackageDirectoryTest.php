<?php

namespace Tests\System\BuildCommand\BuildWithCustomPackageDirectoryTest;

use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\Saeghe\FileManager\File\delete;
use function Saeghe\Saeghe\FileManager\Path\realpath;

test(
    title: 'it should build the project with custom packages directory',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . '/saeghe build --project=TestRequirements/Fixtures/ProjectWithTests');

        assert_file_with_package_dependency_has_been_built('File with package dependency has not been built properly!' . $output);
    },
    before: function () {
        copy(
            realpath($_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests/build-with-custom-packages-directory.json'),
            realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/saeghe.config.json')
        );
        shell_exec("{$_SERVER['PWD']}/saeghe init --project=TestRequirements/Fixtures/ProjectWithTests --packages-directory=vendor");
        shell_exec($_SERVER['PWD'] . '/saeghe add git@github.com:saeghe/simple-package.git --project=TestRequirements/Fixtures/ProjectWithTests');
    },
    after: function () {
        delete_build_directory();
        delete_packages_directory();
        delete(realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/saeghe.config.json'));
        delete(realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/saeghe.config-lock.json'));
    }
);

function delete_build_directory()
{
    delete_recursive(realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds'));
}

function delete_packages_directory()
{
    delete_recursive(realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/vendor'));
}

function assert_file_with_package_dependency_has_been_built($message)
{
    $environment_build_path = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development';
    $stubs_directory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists(realpath($environment_build_path . '/Source/FileUsingVendor.php'))
        && file_get_contents(realpath($environment_build_path . '/Source/FileUsingVendor.php')) === str_replace('$environment_build_path', realpath($environment_build_path), file_get_contents(realpath($stubs_directory . '/Source/FileUsingVendor.stub'))),
        $message
    );
}
