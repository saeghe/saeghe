<?php

namespace Tests\BuildCommandTest;

test(
    title: 'it should build the project',
    case: function () {
        shell_exec($_SERVER['PWD'] . '/saeghe --command=add --project=TestRequirements/Fixtures/ProjectWithTests --path=git@github.com:saeghe/simple-package.git');

        $output = shell_exec($_SERVER['PWD'] . '/saeghe --command=build --project=TestRequirements/Fixtures/ProjectWithTests');

        assertBuildDirectoryExists('Build directory has not been created!' . $output);
        assertEnvironmentBuildDirectoryExists('Environment build directory has not been created!' . $output);
        assertSourceHasBeenBuilt('Source files has not been built properly!' . $output);
        assertFileWithPackageDependencyHasBeenBuilt('File with package dependency has not been built properly!' . $output);
        assertNonePhpFilesHasNotBeenBuilt('None PHP files has been built properly!' . $output);
        assertTestsHasBeenBuilt('Test files has not been built properly!' . $output);
    },
    before: function () {
        deleteBuildDirectory();
        deletePackagesDirectory();
        copy($_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests/build.json', $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/build.json');
    },
    after: function () {
        deleteBuildDirectory();
        deletePackagesDirectory();
        shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/build.json');
        shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/build.lock');
    }
);

function deleteBuildDirectory()
{
    shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds');
}

function deletePackagesDirectory()
{
    shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Packages');
}

function assertBuildDirectoryExists($message)
{
    assert(file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds'), $message);
}

function assertEnvironmentBuildDirectoryExists($message)
{
    assert(file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development'), $message);
}

function assertSourceHasBeenBuilt($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development';
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

function assertNonePhpFilesHasNotBeenBuilt($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development';

    assert(
        file_exists($environmentBuildPath . '/Source/SubDirectory/FileDontNeedBuild.txt')
        && file_get_contents($environmentBuildPath . '/Source/SubDirectory/FileDontNeedBuild.txt') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Source/SubDirectory/FileDontNeedBuild.txt')),
        $message
    );
}

function assertTestsHasBeenBuilt($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development';
    $stubsDirectory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists($environmentBuildPath . '/Tests/SampleTest.php')
        && file_get_contents($environmentBuildPath . '/Tests/SampleTest.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Tests/SampleTest.stub')),
        $message
    );
}

function assertFileWithPackageDependencyHasBeenBuilt($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development';
    $stubsDirectory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists($environmentBuildPath . '/Source/FileWithPackageDependency.php')
        && file_get_contents($environmentBuildPath . '/Source/FileWithPackageDependency.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/FileWithPackageDependency.stub')),
        $message
    );
}
