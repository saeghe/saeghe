<?php

namespace Tests\System\VersionCommandTest;

use function Saeghe\FileManager\Resolver\root;
use function Saeghe\Cli\IO\Write\assert_success;

test(
    title: 'it should show version in the output',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe -v');

        assert_success('Saeghe version 1.15.0', $output);
    }
);

test(
    title: 'it should show version in the output with version option',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe --version');

        assert_success('Saeghe version 1.15.0', $output);
    }
);
