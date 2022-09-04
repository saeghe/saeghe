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
        assertFilePermissionsAreSame('Files permission are not same!' . $output);
        assertGitDirectoryExcluded('Build copied the git directory!' . $output);
        assertExecutablesAreLinked('Executable files did not linked' . $output);
        assertBuildForProjectEntryPoints('Project entry point does not built properly!' . $output);
        assertBuildForPackagesEntryPoints('Packages entry point does not built properly!' . $output);
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

function assertFilePermissionsAreSame($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development';

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

function assertGitDirectoryExcluded($message)
{
    assert(
        ! file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development/.git')
        &&
        ! file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development/Packages/saeghe/simple-package/.git'),
        $message
    );
}

function assertExecutablesAreLinked($message)
{
    $linkFile = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development/run-executable';
    $linkSource = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development/Packages/saeghe/simple-package/run.php';

    assert(
        is_link($linkFile)
        && readlink($linkFile) === $linkSource
        ,
        $message
    );
}

function assertBuildForProjectEntryPoints($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development';
    $stubsDirectory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists($environmentBuildPath . '/entry-point')
        && file_get_contents($environmentBuildPath . '/entry-point') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/entry-point.stub')),
        $message
    );
}

function assertBuildForPackagesEntryPoints($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development';
    $stubsDirectory = $_SERVER['PWD'] . '/TestRequirements/Stubs/SimplePackage';

    assert(
        file_exists($environmentBuildPath . '/Packages/saeghe/simple-package/entry-point')
        && file_get_contents($environmentBuildPath . '/Packages/saeghe/simple-package/entry-point') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/entry-point.stub')),
        $message
    );
}
