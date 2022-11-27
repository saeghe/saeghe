<?php

namespace Tests\System\UpdateCommand\UpdateNotExistsPackageTest;

use function Saeghe\FileManager\Directory\clean;
use function Saeghe\FileManager\Resolver\realpath;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should show error message when package does not exists',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe update git@github.com:saeghe/released-package.git --project=TestRequirements/Fixtures/EmptyProject');

        $expected = <<<EOD
\e[39mUpdating package git@github.com:saeghe/released-package.git to latest version...
\e[39mSetting env credential...
\e[39mLoading configs...
\e[39mFinding package in configs...
\e[91mPackage git@github.com:saeghe/released-package.git does not found in your project!\e[39m

EOD;

        assert_true($expected === $output, 'Output is not correct:' . PHP_EOL . $expected . PHP_EOL . $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/simple-package.git --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);
