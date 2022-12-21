<?php

namespace Tests\System\InstallCommand\InstallCommandTest;

use Saeghe\FileManager\FileType\Json;
use function Saeghe\FileManager\Directory\clean;
use function Saeghe\FileManager\Resolver\root;
use function Saeghe\FileManager\Resolver\realpath;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;
use function Saeghe\TestRunner\Assertions\Boolean\assert_false;

test(
    title: 'it should show error message when the project is not initialized',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe install --project=TestRequirements/Fixtures/EmptyProject');

        $expected = <<<EOD
\e[39mInstalling packages...
\e[91mProject is not initialized. Please try to initialize using the init command.\e[39m

EOD;

        assert_true($expected === $output, 'Output is not correct:' . PHP_EOL . $expected . PHP_EOL . $output);
    }
);

test(
    title: 'it should install packages from lock file',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe install --project=TestRequirements/Fixtures/EmptyProject');

        assert_output($output);
        assert_config_file_content_not_changed('Config file has been changed!' . $output);
        assert_meta_file_content_not_changed('Released Package metadata does not added to meta file properly! ' . $output);
        assert_package_exists_in_packages_directory('Package does not exist in the packages\' directory.' . $output);
        assert_zip_file_deleted('Zip file has not been deleted.' . $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe add git@github.com:saeghe/released-package.git --version=v1.0.3 --project=TestRequirements/Fixtures/EmptyProject');
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages'));
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    },
);

function assert_output($output)
{
    $packages = root() . 'TestRequirements/Fixtures/EmptyProject/Packages/';
    $expected = <<<EOD
\e[39mInstalling packages...
\e[39mSetting env credential...
\e[39mLoading configs...
\e[39mDownloading packages...
\e[39mDownloading package git@github.com:saeghe/released-package.git to {$packages}saeghe/released-package
\e[39mDownloading package git@github.com:saeghe/complex-package to {$packages}saeghe/complex-package
\e[39mDownloading package git@github.com:saeghe/simple-package.git to {$packages}saeghe/simple-package
\e[92mPackages has been installed successfully.\e[39m

EOD;

    assert_true($expected === $output, 'Output is not correct:' . PHP_EOL . $expected . PHP_EOL . $output);
}

function assert_config_file_content_not_changed($message)
{
    $config = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json'));

    assert_true((
            isset($config['packages']['git@github.com:saeghe/released-package.git'])
            && 'v1.0.3' === $config['packages']['git@github.com:saeghe/released-package.git']
        ),
        $message
    );
}

function assert_meta_file_content_not_changed($message)
{
    $meta = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json'));

    assert_true((
            isset($meta['packages']['git@github.com:saeghe/released-package.git'])
            && 'v1.0.3' === $meta['packages']['git@github.com:saeghe/released-package.git']['version']
            && 'saeghe' === $meta['packages']['git@github.com:saeghe/released-package.git']['owner']
            && 'released-package' === $meta['packages']['git@github.com:saeghe/released-package.git']['repo']
            && '9e9b796915596f7c5e0b91d2f9fa5f916a9b5cc8' === $meta['packages']['git@github.com:saeghe/released-package.git']['hash']
        ),
        $message
    );
}

function assert_package_exists_in_packages_directory($message)
{
    assert_true((
            file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package'))
            && file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/saeghe.config.json'))
            && file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/saeghe.config-lock.json'))
            && ! file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package/Tests'))
        ),
        $message
    );
}

function assert_zip_file_deleted($message)
{
    assert_false(file_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/released-package.zip')),
        $message
    );
}
