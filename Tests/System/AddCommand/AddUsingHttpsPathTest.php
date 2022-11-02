<?php

namespace Tests\System\AddCommand\AddUsingHttpsPath;

use Saeghe\Saeghe\FileManager\FileType\Json;
use Saeghe\TestRunner\Assertions\File;
use function Saeghe\Saeghe\FileManager\Directory\flush;
use function Saeghe\Saeghe\FileManager\Path\realpath;

test(
    title: 'it should add package to the project using https url',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe add https://github.com/symfony/thanks.git --project=TestRequirements/Fixtures/EmptyProject');

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
        flush(realpath(root() . 'TestRequirements/Fixtures/EmptyProject'));
    }
);

test(
    title: 'it should add package to the project without trailing .git',
    case: function () {
        $output = shell_exec('php ' . root() . 'saeghe add https://github.com/symfony/thanks.git --project=TestRequirements/Fixtures/EmptyProject');

        assert_http_package_cloned('Http package does not cloned!' . $output);
    },
    before: function () {
        flush(root() . 'TestRequirements/Fixtures/EmptyProject');
        shell_exec('php ' . root() . 'saeghe init --project=TestRequirements/Fixtures/EmptyProject');
    },
    after: function () {
        flush(root() . 'TestRequirements/Fixtures/EmptyProject');
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
    assert(
        File\assert_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/symfony/thanks'))
        && File\assert_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/symfony/thanks/composer.json'))
        && File\assert_exists(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/Packages/symfony/thanks/README.md')),
        $message
    );
}

function assert_http_package_added_to_config($message)
{
    $config = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config.json'));

    assert(
        assert(isset($config['packages']['https://github.com/symfony/thanks.git']))
        && assert('v1.2.10' === $config['packages']['https://github.com/symfony/thanks.git']),
        $message
    );
}

function assert_meta_has_desired_data($message)
{
    $meta = Json\to_array(realpath(root() . 'TestRequirements/Fixtures/EmptyProject/saeghe.config-lock.json'));

    assert(
        isset($meta['packages']['https://github.com/symfony/thanks.git'])
        && 'v1.2.10' === $meta['packages']['https://github.com/symfony/thanks.git']['version']
        && 'symfony' === $meta['packages']['https://github.com/symfony/thanks.git']['owner']
        && 'thanks' === $meta['packages']['https://github.com/symfony/thanks.git']['repo']
        && 'e9c4709560296acbd4fe9e12b8d57a925aa7eae8' === $meta['packages']['https://github.com/symfony/thanks.git']['hash'],
        $message
    );
}
