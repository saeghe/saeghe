<?php

namespace Tests\System\UpdateCommand\UpdateCommandTest;

use Saeghe\FileManager\FileType\Json;
use function Saeghe\FileManager\Directory\clean;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\FileManager\Resolver\realpath;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should update to the latest version',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe update git@github.com:saeghe/released-package.git --project=TestRequirements/Fixtures/EmptyProject');

        $expected = <<<EOD
\e[39mUpdating package git@github.com:saeghe/released-package.git to latest version...
\e[39mSetting env credential...
\e[39mLoading configs...
\e[39mFinding package in configs...
\e[39mSetting package version...
\e[39mLoading package's meta...
\e[39mDeleting package's files...
\e[39mDetecting version hash...
\e[39mDownloading the package with new version...
\e[39mUpdating configs...
\e[39mCommitting new configs...
\e[92mPackage git@github.com:saeghe/released-package.git has been updated.\e[39m

EOD;

        assert_true($expected === $output, 'Output is not correct:' . PHP_EOL . $expected . PHP_EOL . $output);
        assert_version_upgraded_in_config_file($output);
        assert_meta_updated($output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/released-package.git --version=v1.0.3 --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);

function assert_version_upgraded_in_config_file($message)
{
    $config = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json'));

    assert_true((
            isset($config['packages']['git@github.com:saeghe/released-package.git'])
            && 'v1.0.6' === $config['packages']['git@github.com:saeghe/released-package.git']
        ),
        $message
    );
}

function assert_meta_updated($message)
{
    $meta = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json'));

    assert_true((
            isset($meta['packages']['git@github.com:saeghe/released-package.git'])
            && 'v1.0.6' === $meta['packages']['git@github.com:saeghe/released-package.git']['version']
            && 'saeghe' === $meta['packages']['git@github.com:saeghe/released-package.git']['owner']
            && 'released-package' === $meta['packages']['git@github.com:saeghe/released-package.git']['repo']
            && '5885e5f3ed26c2289ceb2eeea1f108f7fbc10c01' === $meta['packages']['git@github.com:saeghe/released-package.git']['hash']
        ),
        $message
    );
}
