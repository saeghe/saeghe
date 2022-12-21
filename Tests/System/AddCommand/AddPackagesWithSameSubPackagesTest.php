<?php

namespace Tests\System\AddCommand\AddPackagesWithSameSubPackagesTest;

use Saeghe\FileManager\FileType\Json;
use function Saeghe\FileManager\Directory\clean;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\FileManager\Resolver\realpath;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should not stuck if two packages using the same dependencies',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/cli.git --project=TestRequirements/Fixtures/EmptyProject');

        assert_output($output);
        assert_true(file_exists(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/cli'));
        assert_true(file_exists(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/test-runner'));
        $config = Json\to_array(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json');
        assert_true((
                isset($config['packages']['git@github.com:saeghe/test-runner.git'])
                && isset($config['packages']['git@github.com:saeghe/cli.git'])
            ),
            'Config file has not been created properly.'
        );
        $meta = Json\to_array(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json');
        assert_true(2 === count($meta['packages']), 'Count of packages in meta file is not correct.');
        assert_true((
                $meta['packages'][array_key_first($meta['packages'])]['repo'] === 'test-runner'
                && $meta['packages'][array_key_last($meta['packages'])]['repo'] === 'cli'
            ),
            'Meta file has not been created properly.'
        );
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/test-runner.git --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);

function assert_output($output)
{
    $expected = <<<EOD
\e[39mAdding package git@github.com:saeghe/cli.git latest version...
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
\e[92mPackage git@github.com:saeghe/cli.git has been added successfully.\e[39m

EOD;

    assert_true($expected === $output, 'Output is not correct:' . PHP_EOL . $expected . PHP_EOL . $output);
}
