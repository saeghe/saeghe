<?php

namespace Tests\System\WatchCommandTest;

use function Saeghe\FileManager\Directory\clean;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\FileManager\Resolver\realpath;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should watch for changes',
    case: function () {
        $command =  'php ' . root() . 'saeghe watch --wait=1 --project=TestRequirements/Fixtures/EmptyProject > /dev/null 2>&1 & echo $!; ';
        $pid = exec($command, $output);

        copy(
            realpath(root() . 'TestRequirements/Stubs/EmptyProjectSource/SimpleClassForEmptyProject.php'),
            realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Source/SimpleClassForEmptyProject.php')
        );

        sleep(2);

        $output = implode(PHP_EOL, $output);

        assert_true(file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/builds')), 'builds directory does not exists! ' . $output);
        assert_true(true ===file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/builds/development')), 'development directory does not exists! ' . $output);
        assert_true(true ===file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/builds/development/Source')), 'Source directory does not exists! ' . $output);
        assert_true(file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/builds/development/Source/SimpleClassForEmptyProject.php')), 'File has not been built! ' . $output);
        posix_kill($pid, SIGKILL);
    },
    before: function () {
        mkdir(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Source'));
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);
