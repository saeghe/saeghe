<?php

namespace Tests\System\AliasCommand\AddUsingAliasTest;

use function Saeghe\FileManager\Directory\clean;
use function Saeghe\FileManager\Resolver\realpath;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should add package using alias',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe add test-runner --project=TestRequirements/Fixtures/EmptyProject');

        $expected = <<<EOD
\e[39mAdding package test-runner latest version...
\e[39mSetting env credential...
\e[39mLoading configs...
\e[39mChecking installed packages...
\e[39mSetting package version...
\e[39mCreating package directory...
\e[39mDetecting version hash...
\e[39mValidating the package...
\e[39mDownloading the package...
\e[39mUpdating configs...
\e[39mCommitting configs...
\e[92mPackage git@github.com:saeghe/test-runner.git has been added successfully.\e[39m

EOD;

        assert_true($expected === $output, 'Output is not correct:' . PHP_EOL . $expected . PHP_EOL . $output);
        assert_true(file_exists(root() .  'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/test-runner'));
        assert_true(file_exists(root() .  'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/test-runner/saeghe.config.json'));
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe alias test-runner git@github.com:saeghe/test-runner.git --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);
