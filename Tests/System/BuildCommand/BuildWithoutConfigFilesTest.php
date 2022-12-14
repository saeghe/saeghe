<?php

namespace Tests\System\BuildCommand\BuildWithoutConfigFilesTest;

use function Saeghe\Cli\IO\Write\assert_success;
use function Saeghe\Datatype\Arr\last;
use function Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\FileManager\File\delete;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\FileManager\Resolver\realpath;

test(
    title: 'it should build project without config files',
    case: function () {
        exec('php ' . root() . 'saeghe build --project=TestRequirements/Fixtures/ProjectWithTests', $output);

        assert_success('Build finished successfully.', last($output) . PHP_EOL);
    },
    before: function () {
        copy(
            realpath(root() . 'TestRequirements/Stubs/ProjectWithTests/saeghe.config.json'),
            realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/saeghe.config.json')
        );
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/simple-package.git --project=TestRequirements/Fixtures/ProjectWithTests');
        delete(realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/Packages/saeghe/simple-package/saeghe.config.json'));
    },
    after: function () {
        delete_build_directory();
        delete_packages_directory();
        delete(realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/saeghe.config.json'));
        delete(realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/saeghe.config-lock.json'));
    }
);

function delete_build_directory()
{
    delete_recursive(realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/builds'));
}

function delete_packages_directory()
{
    delete_recursive(realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/Packages'));
}
