<?php

namespace Tests\BuildCommand\BuildCommandTest;

test(
    title: 'it should build the project',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . '/saeghe build --project=TestRequirements/Fixtures/ProjectWithTests');

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
        assert_exclude_not_built('Excludes has been built!' . $output);
        assert_build_for_extended_classes('Extended classes has not been built properly!' . $output);
        assert_build_for_interfaces('Interfaces has not been built properly!' . $output);
        assert_build_for_traits('Traits has not been built properly!' . $output);
    },
    before: function () {
        delete_build_directory();
        delete_packages_directory();
        copy($_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests/saeghe.config.json', $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/saeghe.config.json');
        shell_exec($_SERVER['PWD'] . '/saeghe add --project=TestRequirements/Fixtures/ProjectWithTests --package=git@github.com:saeghe/simple-package.git');
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
    shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Packages');
}

function assert_build_directory_exists($message)
{
    assert(file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds'), $message);
}

function assert_environment_build_directory_exists($message)
{
    assert(file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development'), $message);
}

function assert_source_has_been_built($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development';
    $stubsDirectory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists($environmentBuildPath . '/Source/SubDirectory/ClassUseAnotherClass.php')
        && file_exists($environmentBuildPath . '/Source/SubDirectory/SimpleClass.php')
        && file_exists($environmentBuildPath . '/Source/SampleFile.php')
        && file_exists($environmentBuildPath . '/Source/ImportingWithTheUseOperator.php')
        && file_exists($environmentBuildPath . '/Source/ImportingMultipleUseStatements.php')
        && file_exists($environmentBuildPath . '/Source/GroupUseStatements.php')
        && file_get_contents($environmentBuildPath . '/Source/SubDirectory/ClassUseAnotherClass.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/SubDirectory/ClassUseAnotherClass.stub'))
        && file_get_contents($environmentBuildPath . '/Source/SubDirectory/SimpleClass.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/SubDirectory/SimpleClass.stub'))
        && file_get_contents($environmentBuildPath . '/Source/SampleFile.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/SampleFile.stub'))
        && file_get_contents($environmentBuildPath . '/Source/ImportingWithTheUseOperator.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/ImportingWithTheUseOperator.stub'))
        && file_get_contents($environmentBuildPath . '/Source/ImportingMultipleUseStatements.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/ImportingMultipleUseStatements.stub'))
        && file_get_contents($environmentBuildPath . '/Source/GroupUseStatements.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/GroupUseStatements.stub'))
        ,
        $message
    );
}

function assert_none_php_files_has_not_been_built($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development';

    assert(
        file_exists($environmentBuildPath . '/Source/SubDirectory/FileDontNeedBuild.txt')
        && file_get_contents($environmentBuildPath . '/Source/SubDirectory/FileDontNeedBuild.txt') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Source/SubDirectory/FileDontNeedBuild.txt')),
        $message
    );
}

function assert_tests_has_been_built($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development';
    $stubsDirectory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists($environmentBuildPath . '/Tests/SampleTest.php')
        && file_get_contents($environmentBuildPath . '/Tests/SampleTest.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Tests/SampleTest.stub')),
        $message
    );
}

function assert_file_with_package_dependency_has_been_built($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development';
    $stubsDirectory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists($environmentBuildPath . '/Source/FileWithPackageDependency.php')
        && file_get_contents($environmentBuildPath . '/Source/FileWithPackageDependency.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/FileWithPackageDependency.stub')),
        $message
    );
}

function assert_file_permissions_are_same($message)
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

function assert_git_directory_excluded($message)
{
    assert(
        ! file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development/.git')
        &&
        ! file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development/Packages/saeghe/simple-package/.git'),
        $message
    );
}

function assert_executables_are_linked($message)
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

function assert_build_for_project_entry_points($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development';
    $stubsDirectory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists($environmentBuildPath . '/entry-point')
        && file_get_contents($environmentBuildPath . '/entry-point') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/entry-point.stub')),
        $message
    );
}

function assert_build_for_packages_entry_points($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development';
    $stubsDirectory = $_SERVER['PWD'] . '/TestRequirements/Stubs/SimplePackage';

    assert(
        file_exists($environmentBuildPath . '/Packages/saeghe/simple-package/entry-point')
        && file_get_contents($environmentBuildPath . '/Packages/saeghe/simple-package/entry-point') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/entry-point.stub')),
        $message
    );
}

function assert_exclude_not_built($message)
{
    assert(
        ! file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development/excluded-file.php')
        && ! file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development/excluded-directory')
        , $message
    );
}

function assert_build_for_extended_classes($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development';
    $stubsDirectory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists($environmentBuildPath . '/Source/ExtendExamples/ParentAbstractClass.php')
        && file_exists($environmentBuildPath . '/Source/ExtendExamples/AbstractClass.php')
        && file_exists($environmentBuildPath . '/Source/ExtendExamples/ParentClass.php')
        && file_exists($environmentBuildPath . '/Source/ExtendExamples/ChildClass.php')
        && file_exists($environmentBuildPath . '/Source/ExtendExamples/ChildFromSource.php')
        && file_get_contents($environmentBuildPath . '/Source/ExtendExamples/ParentAbstractClass.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/ExtendExamples/ParentAbstractClass.stub'))
        && file_get_contents($environmentBuildPath . '/Source/ExtendExamples/AbstractClass.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/ExtendExamples/AbstractClass.stub'))
        && file_get_contents($environmentBuildPath . '/Source/ExtendExamples/ParentClass.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/ExtendExamples/ParentClass.stub'))
        && file_get_contents($environmentBuildPath . '/Source/ExtendExamples/ChildClass.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/ExtendExamples/ChildClass.stub'))
        && file_get_contents($environmentBuildPath . '/Source/ExtendExamples/ChildFromSource.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/ExtendExamples/ChildFromSource.stub'))
        ,
        $message
    );
}

function assert_build_for_interfaces($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development';
    $stubsDirectory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists($environmentBuildPath . '/Source/InterfaceExamples/InnerInterfaces/InnerInterface.php')
        && file_exists($environmentBuildPath . '/Source/InterfaceExamples/InnerInterfaces/OtherInnerInterface.php')
        && file_exists($environmentBuildPath . '/Source/InterfaceExamples/FirstInterface.php')
        && file_exists($environmentBuildPath . '/Source/InterfaceExamples/MyClass.php')
        && file_exists($environmentBuildPath . '/Source/InterfaceExamples/SecondInterface.php')
        && file_exists($environmentBuildPath . '/Source/InterfaceExamples/ThirdInterface.php')
        && file_get_contents($environmentBuildPath . '/Source/InterfaceExamples/InnerInterfaces/InnerInterface.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/InterfaceExamples/InnerInterfaces/InnerInterface.stub'))
        && file_get_contents($environmentBuildPath . '/Source/InterfaceExamples/InnerInterfaces/OtherInnerInterface.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/InterfaceExamples/InnerInterfaces/OtherInnerInterface.stub'))
        && file_get_contents($environmentBuildPath . '/Source/InterfaceExamples/FirstInterface.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/InterfaceExamples/FirstInterface.stub'))
        && file_get_contents($environmentBuildPath . '/Source/InterfaceExamples/MyClass.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/InterfaceExamples/MyClass.stub'))
        && file_get_contents($environmentBuildPath . '/Source/InterfaceExamples/SecondInterface.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/InterfaceExamples/SecondInterface.stub'))
        && file_get_contents($environmentBuildPath . '/Source/InterfaceExamples/ThirdInterface.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/InterfaceExamples/ThirdInterface.stub'))
        ,
        $message
    );
}

function assert_build_for_traits($message)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development';
    $stubsDirectory = $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests';

    assert(
        file_exists($environmentBuildPath . '/Source/UsedTraits/ClassWithTrait.php')
        && file_exists($environmentBuildPath . '/Source/UsedTraits/FirstTrait.php')
        && file_exists($environmentBuildPath . '/Source/UsedTraits/SecondTrait.php')
        && file_exists($environmentBuildPath . '/Source/UsedTraits/ThirdTrait.php')
        && file_get_contents($environmentBuildPath . '/Source/UsedTraits/ClassWithTrait.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/UsedTraits/ClassWithTrait.stub'))
        && file_get_contents($environmentBuildPath . '/Source/UsedTraits/FirstTrait.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/UsedTraits/FirstTrait.stub'))
        && file_get_contents($environmentBuildPath . '/Source/UsedTraits/SecondTrait.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/UsedTraits/SecondTrait.stub'))
        && file_get_contents($environmentBuildPath . '/Source/UsedTraits/ThirdTrait.php') === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/Source/UsedTraits/ThirdTrait.stub'))
        ,
        $message
    );
}
