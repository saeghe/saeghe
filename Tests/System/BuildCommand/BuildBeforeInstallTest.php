<?php

namespace Tests\System\BuildCommand\BuildBeforeInstallTest;

use function Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\FileManager\File\delete;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\FileManager\Resolver\realpath;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should show error message when project packages are not installed',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe build --project=TestRequirements/Fixtures/ProjectWithTests');

        assert_output($output);
    },
    before: function () {
        copy(
            realpath(root() . 'TestRequirements/Stubs/ProjectWithTests/saeghe.config.json'),
            realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/saeghe.config.json')
        );
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/simple-package.git --project=TestRequirements/Fixtures/ProjectWithTests');
        delete_recursive(root() . 'TestRequirements/Fixtures/ProjectWithTests/Packages/saeghe/simple-package');
    },
    after: function () {
        delete_packages_directory();
        delete(realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/saeghe.config.json'));
        delete(realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/saeghe.config-lock.json'));
    }
);

function assert_output($output)
{
    $expected = <<<EOD
\e[39mStart building...
\e[39mReading configs...
\e[39mChecking packages...
\e[91mIt seems you didn't run the install command. Please make sure you installed your required packages.\e[39m

EOD;

    assert_true($output === $expected, 'Command output is not correct.' . PHP_EOL . $output . PHP_EOL . $expected);
}

function delete_build_directory()
{
    delete_recursive(realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/builds'));
}

function delete_packages_directory()
{
    delete_recursive(realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/Packages'));
}
