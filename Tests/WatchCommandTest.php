<?php

namespace Tests\WatchCommandTest;

test(
    title: 'it should watch for changes',
    case: function () {
        $command =  $_SERVER['PWD'] . '/saeghe --command=watch --wait=1 --project=TestRequirements/Fixtures/EmptyProject > /dev/null 2>&1 & echo $!; ';
        $pid = exec($command, $output);

        copy($_SERVER['PWD'] . '/TestRequirements/Stubs/EmptyProjectSource/SimpleClassForEmptyProject.php', $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Source/SimpleClassForEmptyProject.php');

        sleep(2);

        $output = implode(PHP_EOL, $output);

        assert(file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/builds'), 'builds directory does not exists! ' . $output);
        assert(file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/builds/development'), 'development directory does not exists! ' . $output);
        assert(file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/builds/development/Source'), 'Source directory does not exists! ' . $output);
        assert(file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/builds/development/Source/SimpleClassForEmptyProject.php'), 'File has not been built! ' . $output);
        posix_kill($pid, SIGKILL);
    },
    before: function () {
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/*');
        shell_exec($_SERVER['PWD'] . '/saeghe --command=init --project=TestRequirements/Fixtures/ProjectWithTests');
        mkdir($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Source');
    },
    after: function () {
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/*');
    }
);