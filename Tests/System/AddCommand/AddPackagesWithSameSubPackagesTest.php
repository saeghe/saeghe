<?php

namespace Tests\System\AddCommand\AddPackagesWithSameSubPackagesTest;

use function Saeghe\Cli\IO\Write\assert_success;
use function Saeghe\Saeghe\FileManager\Directory\flush;

test(
    title: 'it should not stuck if two packages using the same dependencies',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe add git@github.com:saeghe/cli.git --project=TestRequirements/Fixtures/EmptyProject");

        assert_success('Package git@github.com:saeghe/cli.git has been added successfully.', $output);
        assert(file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/cli'));
        assert(file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject/Packages/saeghe/test-runner'));
    },
    before: function () {
        shell_exec($_SERVER['PWD'] . "/saeghe init --project=TestRequirements/Fixtures/EmptyProject");
    },
    after: function () {
        flush($_SERVER['PWD'] . '/TestRequirements/Fixtures/EmptyProject');
    }
);
