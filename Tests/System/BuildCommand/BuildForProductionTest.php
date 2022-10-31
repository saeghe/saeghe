<?php

namespace Tests\System\BuildCommand\BuildForProductionTest;

use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\Saeghe\FileManager\File\delete;

test(
    title: 'it should build the project',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . '/saeghe build production --project=TestRequirements/Fixtures/ProjectWithTests');

        assert_build_directory_exists('Build directory has not been created!' . $output);
        assert_environment_build_directory_exists('Environment build directory has not been created!' . $output);
        assert_source_has_been_built('Source files has not been built properly!' . $output);
        assert_file_with_package_dependency_has_been_built('File with package dependency has not been built properly!' . $output);
        assert_none_php_files_has_not_been_built('None PHP files has been built properly!' . $output);
        assert_tests_has_been_built('Test files has not been built properly!' . $output);
        assert_file_permissions_are_same('Files permission are not same!' . $output);
        assert_git_directory_excluded('Build copied the git directory!' . $output);
        assert_executables_are_linked('Executable files did not linked' . $output);
        assert_build_for_project_entry_points('Project entry point does not built properly!' . $output);
        assert_build_for_packages_entry_points('Packages entry point does not built properly!' . $output);
    },
    before: function () {
        copy(
            $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests/saeghe.config.json',
            $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/saeghe.config.json'
        );
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
    delete_recursive($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Packages');
}

function assert_build_directory_exists($message)
{
    assert(file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds'), $message);
}

function assert_environment_build_directory_exists($message)
{
    assert(file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production'), $message);
}

function assert_source_has_been_built($message)
{
    $environment_build_path = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production';
    $stubs_directory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists($environment_build_path . '/Source/SubDirectory/ClassUseAnotherClass.php')
        && file_exists($environment_build_path . '/Source/SubDirectory/SimpleClass.php')
        && file_exists($environment_build_path . '/Source/SampleFile.php')
        && file_get_contents($environment_build_path . '/Source/SubDirectory/ClassUseAnotherClass.php') === str_replace('$environment_build_path', $environment_build_path, file_get_contents($stubs_directory . '/Source/SubDirectory/ClassUseAnotherClass.stub'))
        && file_get_contents($environment_build_path . '/Source/SubDirectory/SimpleClass.php') === str_replace('$environment_build_path', $environment_build_path, file_get_contents($stubs_directory . '/Source/SubDirectory/SimpleClass.stub'))
        && file_get_contents($environment_build_path . '/Source/SampleFile.php') === str_replace('$environment_build_path', $environment_build_path, file_get_contents($stubs_directory . '/Source/SampleFile.stub'))
        ,
        $message
    );
}

function assert_none_php_files_has_not_been_built($message)
{
    $environment_build_path = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production';

    assert(
        file_exists($environment_build_path . '/Source/SubDirectory/FileDontNeedBuild.txt')
        && file_get_contents($environment_build_path . '/Source/SubDirectory/FileDontNeedBuild.txt') === str_replace('$environment_build_path', $environment_build_path, file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Source/SubDirectory/FileDontNeedBuild.txt')),
        $message
    );
}

function assert_tests_has_been_built($message)
{
    $environment_build_path = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production';
    $stubs_directory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists($environment_build_path . '/Tests/SampleTest.php')
        && file_get_contents($environment_build_path . '/Tests/SampleTest.php') === str_replace('$environment_build_path', $environment_build_path, file_get_contents($stubs_directory . '/Tests/SampleTest.stub')),
        $message
    );
}

function assert_file_with_package_dependency_has_been_built($message)
{
    $environment_build_path = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production';
    $stubs_directory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists($environment_build_path . '/Source/FileWithPackageDependency.php')
        && file_get_contents($environment_build_path . '/Source/FileWithPackageDependency.php') === str_replace('$environment_build_path', $environment_build_path, file_get_contents($stubs_directory . '/Source/FileWithPackageDependency.stub')),
        $message
    );
}

function assert_file_permissions_are_same($message)
{
    $environment_build_path = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production';

    assert(
        fileperms($environment_build_path . '/PublicDirectory')
        ===
        fileperms($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/PublicDirectory'),
        'Directory permission is not set properly!' . $message
    );
    assert(
        fileperms($environment_build_path . '/PublicDirectory/ExecutableFile.php')
        ===
        fileperms($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/PublicDirectory/ExecutableFile.php'),
        'PHP file permission is not set properly!' . $message
    );
    assert(
        fileperms($environment_build_path . '/PublicDirectory/AnotherExecutableFile')
        ===
        fileperms($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/PublicDirectory/AnotherExecutableFile'),
        'Other file permission is not set properly!' . $message
    );
}

function assert_git_directory_excluded($message)
{
    assert(
        ! file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production/.git')
        &&
        ! file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production/Packages/saeghe/simple-package/.git'),
        $message
    );
}

function assert_executables_are_linked($message)
{
    $link_file = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production/run-executable';
    $link_source = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production/Packages/saeghe/simple-package/run.php';

    assert(
        is_link($link_file)
        && readlink($link_file) === $link_source
        ,
        $message
    );
}

function assert_build_for_project_entry_points($message)
{
    $environment_build_path = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production';
    $stubs_directory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists($environment_build_path . '/entry-point')
        && file_get_contents($environment_build_path . '/entry-point') === str_replace('$environment_build_path', $environment_build_path, file_get_contents($stubs_directory . '/entry-point.stub')),
        $message
    );
}

function assert_build_for_packages_entry_points($message)
{
    $environment_build_path = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production';
    $stubs_directory = $_SERVER['PWD'] . '/TestRequirements/Stubs/SimplePackage';

    assert(
        file_exists($environment_build_path . '/Packages/saeghe/simple-package/entry-point')
        && file_get_contents($environment_build_path . '/Packages/saeghe/simple-package/entry-point') === str_replace('$environment_build_path', $environment_build_path, file_get_contents($stubs_directory . '/entry-point.stub')),
        $message
    );
}
