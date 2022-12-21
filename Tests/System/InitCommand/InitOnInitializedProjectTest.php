<?php

namespace Tests\System\InitCommand\InitOnInitializedProject;

use function Saeghe\FileManager\Directory\clean;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should return error when project is already initialized',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');

        $expected = <<<EOD
\e[39mInit project...
\e[91mThe project is already initialized.\e[39m

EOD;

        assert_true($expected === $output, 'Output is not correct:' . PHP_EOL . $expected . PHP_EOL . $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);
