<?php

namespace Tests\System\BuildCommand\BuildProjectWithComplexPackageTest;

use function Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\FileManager\File\delete;
use function Saeghe\FileManager\Resolver\realpath;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should build project with complex package',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe build --project=TestRequirements/Fixtures/ProjectWithTests');
        assert_build_for_packages('Packages file does not built properly!' . $output);
        assert_build_for_dependency_packages('Dependency Packages file does not built properly!' . $output);
        assert_executable_file_added('Complex executable file has not been created!' . $output);
    },
    before: function () {
        copy(
            realpath(root() . 'TestRequirements/Stubs/ProjectWithTests/saeghe.config.json'),
            realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/saeghe.config.json')
        );
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/complex-package.git --project=TestRequirements/Fixtures/ProjectWithTests');
    },
    after: function () {
        delete_config_file();
        delete_meta_file();
        delete_build_directory();
        delete_packages_directory();
    },
);

function delete_config_file()
{
    delete(realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/saeghe.config.json'));
}

function delete_meta_file()
{
    delete(realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/saeghe.config-lock.json'));
}

function delete_build_directory()
{
    delete_recursive(realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/builds'));
}

function delete_packages_directory()
{
    delete_recursive(realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/Packages'));
}

function assert_build_for_packages($message)
{
    assert_true((
            build_exists_and_same_as_stub('src/Controllers/Controller.php')
            && build_exists_and_same_as_stub('src/Controllers/HomeController.php')
            && build_exists_and_same_as_stub('src/Models/User.php')
            && build_exists_and_same_as_stub('src/Views/home.php')
            && build_exists_and_same_as_stub('src/Helpers.php')
            && build_exists_and_same_as_stub('tests/Features/FirstFeature.php')
            && build_exists_and_same_as_stub('tests/TestHelper.php')
            && build_exists_and_same_as_stub('saeghe.config.json')
            && build_exists_and_same_as_stub('saeghe.config-lock.json')
            && build_exists_and_same_as_stub('cli-command')
        ),
        $message
    );
}

function assert_build_for_dependency_packages($message)
{
    $environment_build_path = root() . 'TestRequirements/Fixtures/ProjectWithTests/builds/development';
    $stubs_directory = root() . 'TestRequirements/Stubs/BuildForComplexPackage';
    assert_true(
        file_get_contents(realpath($environment_build_path . '/Packages/saeghe/simple-package/run.php'))
        === str_replace('$environment_build_path', realpath($environment_build_path), file_get_contents(realpath($stubs_directory . '/' . 'simple-package.run.php.stub')))
        ,
        $message
    );
}

function build_exists_and_same_as_stub($file)
{
    $environment_build_path = root() . 'TestRequirements/Fixtures/ProjectWithTests/builds/development';
    $stubs_directory = root() . 'TestRequirements/Stubs/BuildForComplexPackage';

    return
        file_exists(realpath($environment_build_path . '/Packages/saeghe/complex-package/' . $file))
        && file_get_contents(realpath($environment_build_path . '/Packages/saeghe/complex-package/' . $file)) === str_replace('$environment_build_path', realpath($environment_build_path), file_get_contents(realpath($stubs_directory . '/' . $file . '.stub')));
}

function assert_executable_file_added($message)
{
    assert_true((
            is_link(realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/builds/development/complex'))
            && readlink(realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/builds/development/complex'))
            === realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/builds/development/Packages/saeghe/complex-package/cli-command')
        ),
        $message
    );
}
