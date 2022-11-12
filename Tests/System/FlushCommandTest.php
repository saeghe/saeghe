<?php

namespace Tests\System\FlushCommandTest;

use function Saeghe\Cli\IO\Write\assert_success;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\Saeghe\FileManager\Resolver\realpath;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should flush builds',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe flush --project=TestRequirements/Fixtures/ProjectWithTests');

        assert_development_build_is_empty('Development build directory is not empty.' . $output);
        assert_production_build_is_empty('Production build directory is not empty.' . $output);
        assert_success('Build directory has been flushed.', $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe build --project=TestRequirements/Fixtures/ProjectWithTests');
        shell_exec('php ' . root() . 'saeghe build production --project=TestRequirements/Fixtures/ProjectWithTests');
    },
    after: function () {
        delete_recursive(realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/builds'));
    }
);

function assert_development_build_is_empty($message)
{
    assert_true(['.', '..'] === scandir(realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/builds/development')), $message);
}

function assert_production_build_is_empty($message)
{
    assert_true(['.', '..'] === scandir(realpath(root() . 'TestRequirements/Fixtures/ProjectWithTests/builds/production')), $message);
}
