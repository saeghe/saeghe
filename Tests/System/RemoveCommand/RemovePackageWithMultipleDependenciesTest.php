<?php

namespace Tests\System\RemoveCommand\RemovePackageWithMultipleDependenciesTest;

use function Saeghe\FileManager\Directory\clean;
use function Saeghe\FileManager\Resolver\realpath;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\TestRunner\Assertions\Boolean\assert_false;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should remove package with multiple dependencies',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe remove git@github.com:saeghe/datatype.git --project=TestRequirements/Fixtures/EmptyProject');
        $expected = <<<EOD
\e[39mRemoving package git@github.com:saeghe/datatype.git
\e[39mLoading configs...
\e[39mFinding package in configs...
\e[39mLoading package's config...
\e[39mRemoving package from config...
\e[39mCommitting configs...
\e[92mPackage git@github.com:saeghe/datatype.git has been removed successfully.\e[39m

EOD;

        assert_true($expected === $output, 'Output is not correct:' . PHP_EOL . $expected . PHP_EOL . $output);
        assert_false(file_exists(root() .  'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/datatype'));
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/datatype.git --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);
