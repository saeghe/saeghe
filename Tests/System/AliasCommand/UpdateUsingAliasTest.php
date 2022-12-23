<?php

namespace Tests\System\AliasCommand\UpdateUsingAliasTest;

use function Saeghe\FileManager\Directory\clean;
use function Saeghe\FileManager\FileType\Json\to_array;
use function Saeghe\FileManager\Resolver\realpath;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\TestRunner\Assertions\Boolean\assert_false;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should remove package using alias',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe update datatype --project=TestRequirements/Fixtures/EmptyProject');
        $expected = <<<EOD
\e[39mUpdating package datatype to latest version...
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
\e[92mPackage git@github.com:saeghe/datatype.git has been updated.\e[39m

EOD;

        assert_true($expected === $output, 'Output is not correct:' . PHP_EOL . $expected . PHP_EOL . $output);
        assert_true(file_exists(root() .  'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/datatype'));
        $config_file = root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json';
        assert_false(
            'v1.3.0'
            ===
            to_array($config_file)['packages']['git@github.com:saeghe/datatype.git']
        );
        $meta_file = root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json';
        assert_false(
            'f527b3145dd30689075f5171566dfeee6809640b'
            ===
            to_array($meta_file)['packages']['git@github.com:saeghe/datatype.git']
        );
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe alias datatype git@github.com:saeghe/datatype.git --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add datatype v1.3.0 --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);
