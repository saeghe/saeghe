<?php

namespace Tests\System\RemoveCommand\RemoveCommandTest;

use Saeghe\FileManager\FileType\Json;
use function Saeghe\FileManager\Directory\clean;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\FileManager\Resolver\realpath;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should show error message when the project is not initialized',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe remove git@github.com:saeghe/simple-package.git --project=TestRequirements/Fixtures/EmptyProject');

        $expected = <<<EOD
\e[39mRemoving package git@github.com:saeghe/simple-package.git
\e[91mProject is not initialized. Please try to initialize using the init command.\e[39m

EOD;

        assert_true($expected === $output, 'Output is not correct:' . PHP_EOL . $expected . PHP_EOL . $output);
    }
);

test(
    title: 'it should remove a package',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe remove git@github.com:saeghe/complex-package.git --project=TestRequirements/Fixtures/EmptyProject');

        assert_success_output($output);
        assert_desired_data_in_packages_directory('Package has not been deleted from Packages directory!' . $output);
        assert_config_file_is_clean('Packages has not been deleted from config file!' . $output);
        assert_meta_is_clean('Packages has not been deleted from meta!' . $output);

        $output = shell_exec('php ' . root() . 'saeghe remove git@github.com:saeghe/complex-package.git --project=TestRequirements/Fixtures/EmptyProject');

        assert_error_output($output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/complex-package.git --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);

function assert_success_output($output)
{
    $expected = <<<EOD
\e[39mRemoving package git@github.com:saeghe/complex-package.git
\e[39mLoading configs...
\e[39mFinding package in configs...
\e[39mLoading package's config...
\e[39mRemoving package from config...
\e[39mCommitting configs...
\e[92mPackage git@github.com:saeghe/complex-package.git has been removed successfully.\e[39m

EOD;

    assert_true($expected === $output, 'Output is not correct:' . PHP_EOL . $expected . PHP_EOL . $output);
}

function assert_error_output($output)
{
    $expected = <<<EOD
\e[39mRemoving package git@github.com:saeghe/complex-package.git
\e[39mLoading configs...
\e[39mFinding package in configs...
\e[91mPackage git@github.com:saeghe/complex-package.git does not found in your project!\e[39m

EOD;

    assert_true($expected === $output, 'Output is not correct:' . PHP_EOL . $expected . PHP_EOL . $output);
}

function assert_desired_data_in_packages_directory($message)
{
    clearstatcache();
    assert_true((
            ! file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/simple-package'))
            && ! file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/complex-package'))
        ),
        $message
    );
}

function assert_config_file_is_clean($message)
{
    $config = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json'));

    assert_true($config['packages'] === [], $message);
}

function assert_meta_is_clean($message)
{
    $config = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json'));

    assert_true($config['packages'] === [], $message);
}
