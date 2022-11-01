<?php

namespace Tests\System\WatchCommandTest;

use function Saeghe\Saeghe\FileManager\Directory\flush;
use function Saeghe\Saeghe\FileManager\Path\realpath;

test(
    title: 'it should watch for changes',
    case: function () {
        $command =  $_SERVER['PWD'] . '/saeghe watch --wait=1 --project=TestRequirements/Fixtures/EmptyProject > /dev/null 2>&1 & echo $!; ';
        $pid = exec($command, $output);

        copy(
            realpath($_SERVER['PWD'] . '/TestRequirements/Stubs/EmptyProjectSource/SimpleClassForEmptyProject.php'),
            realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Source/SimpleClassForEmptyProject.php')
        );

        sleep(2);

        $output = implode(PHP_EOL, $output);

        assert(file_exists(realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/builds')), 'builds directory does not exists! ' . $output);
        assert(file_exists(realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/builds/development')), 'development directory does not exists! ' . $output);
        assert(file_exists(realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/builds/development/Source')), 'Source directory does not exists! ' . $output);
        assert(file_exists(realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/builds/development/Source/SimpleClassForEmptyProject.php')), 'File has not been built! ' . $output);
        posix_kill($pid, SIGKILL);
    },
    before: function () {
        mkdir(realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Source'));
    },
    after: function () {
        flush(realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject'));
    }
);
