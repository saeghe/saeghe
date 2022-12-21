<?php

namespace Tests\System\UpdateCommand\UpdatePackagesWithSharedDependenciesTest;

use function Saeghe\FileManager\Directory\clean;
use function Saeghe\FileManager\Resolver\realpath;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should update packages with shared dependencies',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe update git@github.com:saeghe/file-manager.git --project=TestRequirements/Fixtures/EmptyProject');

        $expected = <<<EOD
\e[39mUpdating package git@github.com:saeghe/file-manager.git to latest version...
\e[39mSetting env credential...
\e[39mLoading configs...
\e[39mFinding package in configs...
\e[39mSetting package version...
\e[39mLoading package's config...
\e[39mDeleting package's files...
\e[39mDetecting version hash...
\e[39mDownloading the package with new version...
\e[39mUpdating configs...
\e[39mCommitting new configs...
\e[92mPackage git@github.com:saeghe/file-manager.git has been updated.\e[39m

EOD;

        assert_true($expected === $output, 'Output is not correct:' . PHP_EOL . $expected . PHP_EOL . $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/test-runner.git --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/datatype.git --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/file-manager.git --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);
