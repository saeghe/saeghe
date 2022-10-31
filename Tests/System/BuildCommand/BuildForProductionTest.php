<?php

namespace Tests\System\BuildCommand\BuildForProductionTest;

use Saeghe\Saeghe\Path;
use function Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\FileManager\File\delete;

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
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production';
    $stubsDirectory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists($environmentBuildPath . '/Source/SubDirectory/ClassUseAnotherClass.php')
        && file_exists($environmentBuildPath . '/Source/SubDirectory/SimpleClass.php')
        && file_exists($environmentBuildPath . '/Source/SampleFile.php')
        && file_get_contents($environmentBuildPath . '/Source/SubDirectory/ClassUseAnotherClass.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/SubDirectory/ClassUseAnotherClass.stub'))
        && file_get_contents($environmentBuildPath . '/Source/SubDirectory/SimpleClass.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/SubDirectory/SimpleClass.stub'))
        && file_get_contents($environmentBuildPath . '/Source/SampleFile.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/SampleFile.stub'))
        ,
        $message
    );
}

function assert_none_php_files_has_not_been_built($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production';

    assert(
        file_exists($environmentBuildPath . '/Source/SubDirectory/FileDontNeedBuild.txt')
        && file_get_contents($environmentBuildPath . '/Source/SubDirectory/FileDontNeedBuild.txt') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Source/SubDirectory/FileDontNeedBuild.txt')),
        $message
    );
}

function assert_tests_has_been_built($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production';
    $stubsDirectory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists($environmentBuildPath . '/Tests/SampleTest.php')
        && file_get_contents($environmentBuildPath . '/Tests/SampleTest.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Tests/SampleTest.stub')),
        $message
    );
}

function assert_file_with_package_dependency_has_been_built($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production';
    $stubsDirectory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists($environmentBuildPath . '/Source/FileWithPackageDependency.php')
        && file_get_contents($environmentBuildPath . '/Source/FileWithPackageDependency.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/FileWithPackageDependency.stub')),
        $message
    );
}

function assert_file_permissions_are_same($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production';

    assert(
        fileperms($environmentBuildPath . '/PublicDirectory')
        ===
        fileperms($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/PublicDirectory'),
        'Directory permission is not set properly!' . $message
    );
    assert(
        fileperms($environmentBuildPath . '/PublicDirectory/ExecutableFile.php')
        ===
        fileperms($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/PublicDirectory/ExecutableFile.php'),
        'PHP file permission is not set properly!' . $message
    );
    assert(
        fileperms($environmentBuildPath . '/PublicDirectory/AnotherExecutableFile')
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
    $linkFile = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production/run-executable';
    $linkSource = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production/Packages/saeghe/simple-package/run.php';

    assert(
        is_link($linkFile)
        && readlink($linkFile) === $linkSource
        ,
        $message
    );
}

function assert_build_for_project_entry_points($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production';
    $stubsDirectory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists($environmentBuildPath . '/entry-point')
        && file_get_contents($environmentBuildPath . '/entry-point') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/entry-point.stub')),
        $message
    );
}

function assert_build_for_packages_entry_points($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/production';
    $stubsDirectory = $_SERVER['PWD'] . '/TestRequirements/Stubs/SimplePackage';

    assert(
        file_exists($environmentBuildPath . '/Packages/saeghe/simple-package/entry-point')
        && file_get_contents($environmentBuildPath . '/Packages/saeghe/simple-package/entry-point') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/entry-point.stub')),
        $message
    );
}
