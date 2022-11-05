<?php

namespace Tests\System\AddCommand\AddUsingHttpsPath;

use Saeghe\Saeghe\FileManager\FileType\Json;
use Saeghe\TestRunner\Assertions\File;
use function Saeghe\Saeghe\FileManager\Directory\clean;
use function Saeghe\Saeghe\FileManager\Path\realpath;

test(
    title: 'it should add package to the project using https url',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe add https://github.com/saeghe/cli.git --version=v2.0.0 --project=TestRequirements/Fixtures/EmptyProject');

        assert_config_file_created_for_http_project('Config file is not created!' . $output);
        assert_http_package_added_to_config('Http Package does not added to config file properly! ' . $output);
        assert_packages_directory_created_for_empty_project('Package directory does not created.' . $output);
        assert_http_package_cloned('Http package does not cloned!' . $output);
        assert_meta_has_desired_data('Meta data is not what we want.' . $output);
    },
    before: function () {
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);

test(
    title: 'it should add package to the project without trailing .git',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe add https://github.com/saeghe/cli --version=v2.0.0 --project=TestRequirements/Fixtures/EmptyProject');

        assert_http_package_cloned('Http package does not cloned!' . $output);
    },
    before: function () {
        clean(root() . 'TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        clean(root() . 'TestRequirements/Fixtures/EmptyProject');
    }
);

function assert_config_file_created_for_http_project($message)
{
    File\assert_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json'), $message);
}

function assert_packages_directory_created_for_empty_project($message)
{
    File\assert_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages'), $message);
}

function assert_http_package_cloned($message)
{
    assert_true((
            File\assert_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/cli'))
            && File\assert_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/cli/saeghe.config.json'))
            && File\assert_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/saeghe/cli/saeghe.config-lock.json'))
        ),
        $message
    );
}

function assert_http_package_added_to_config($message)
{
    $config = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json'));

    assert_true((
            isset($config['packages']['https://github.com/saeghe/cli.git'])
            && 'v2.0.0' === $config['packages']['https://github.com/saeghe/cli.git']
        ),
        $message
    );
}

function assert_meta_has_desired_data($message)
{
    $meta = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json'));

    assert_true((
            isset($meta['packages']['https://github.com/saeghe/cli.git'])
            && 'v2.0.0' === $meta['packages']['https://github.com/saeghe/cli.git']['version']
            && 'saeghe' === $meta['packages']['https://github.com/saeghe/cli.git']['owner']
            && 'cli' === $meta['packages']['https://github.com/saeghe/cli.git']['repo']
            && '1c4c0fbbe320574a135931c9cd59d7f0d1c03754' === $meta['packages']['https://github.com/saeghe/cli.git']['hash']
        ),
        $message
    );
}
