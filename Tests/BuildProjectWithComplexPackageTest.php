<?php

namespace Tests\BuildProjectWithComplexPackageTest;

test(
    title: 'it should build project with complex package',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=build --project=TestRequirements/Fixtures/ProjectWithTests");
        assertBuildForPackages('Packages file does not built properly!' . $output);
        assertExecutableFileAdded('Complex executable file has not been created!' . $output);
    },
    before: function () {
        deleteBuildJson();
        deleteBuildLock();
        deleteBuildDirectory();
        deletePackagesDirectory();

        copy($_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests/build.json', $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/build.json');
        shell_exec($_SERVER['PWD'] . "/saeghe --command=add --project=TestRequirements/Fixtures/ProjectWithTests --path=git@github.com:saeghe/complex-package.git");
    },
    after: function () {
        deleteBuildJson();
        deleteBuildLock();
        deleteBuildDirectory();
        deletePackagesDirectory();
    },
);

function deleteBuildJson()
{
    shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/build.json');
}

function deleteBuildLock()
{
    shell_exec('rm -f ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/build.lock');
}

function deleteBuildDirectory()
{
    shell_exec('rm -Rf ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds');
}

function deletePackagesDirectory()
{
    shell_exec('rm -Rf ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Packages');
}

function assertBuildForPackages($message)
{
   assert(
       buildExistsAndSameAsStub('src/Controllers/Controller.php')
       && buildExistsAndSameAsStub('src/Controllers/HomeController.php')
       && buildExistsAndSameAsStub('src/Models/User.php')
       && buildExistsAndSameAsStub('src/Views/home.php')
       && buildExistsAndSameAsStub('src/Helpers.php')
       && buildExistsAndSameAsStub('tests/Features/FirstFeature.php')
       && buildExistsAndSameAsStub('tests/TestHelper.php')
       && buildExistsAndSameAsStub('build.json')
       && buildExistsAndSameAsStub('build.lock')
       && buildExistsAndSameAsStub('cli-command')
       ,
       $message
   );
}

function buildExistsAndSameAsStub($file)
{
    $environmentBuildPath = $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development';
    $stubsDirectory = $_SERVER['PWD'] . '/TestRequirements/Stubs/BuildForComplexPackage';

    return
        file_exists($environmentBuildPath . '/Packages/saeghe/complex-package/' . $file)
        && file_get_contents($environmentBuildPath . '/Packages/saeghe/complex-package/' . $file) === str_replace('$environmentBuildPath', $environmentBuildPath, file_get_contents($stubsDirectory . '/' . $file . '.stub'));
}

function assertExecutableFileAdded($message)
{
    assert(
        is_link($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development/complex')
        && readlink($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development/complex')
             === $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/builds/development/Packages/saeghe/complex-package/cli-command'
        ,
        $message
    );
}
