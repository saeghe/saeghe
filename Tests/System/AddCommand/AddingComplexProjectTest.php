<?php

namespace Tests\System\AddCommand\AddingComplexProjectTest;

use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\Saeghe\FileManager\File\delete;

test(
    title: 'it should add a complex project',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe add git@github.com:saeghe/complex-package.git --project=TestRequirements/Fixtures/ProjectWithTests");

        assert_pacakges_added_to_packages_directory('Packages does not added to the packages directory!' . $output);
        assert_config_file_has_desired_data('Config file for adding complex package does not have desired data!' . $output);
        assert_meta_file_has_desired_data('Meta data for adding complex package does not have desired data!' . $output);
    },
    before: function () {
        copy(
            $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests/saeghe.config.json',
            $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/saeghe.config.json'
        );
    },
    after: function () {
        delete_config_file();
        delete_meta_file();
        delete_packages_directory();
    }
);

test(
    title: 'it should add a complex project with http path',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe add https://github.com/saeghe/complex-package.git --project=TestRequirements/Fixtures/ProjectWithTests");

        assert_pacakges_added_to_packages_directory('Packages does not added to the packages directory!' . $output);
    },
    before: function () {
        copy(
            $_SERVER['PWD'] . '/TestRequirements/Stubs/ProjectWithTests/saeghe.config.json',
            $_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/saeghe.config.json'
        );
    },
    after: function () {
        delete_config_file();
        delete_meta_file();
        delete_packages_directory();
    }
);

function delete_config_file()
{
    delete($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/saeghe.config.json');
}

function delete_meta_file()
{
    delete($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/saeghe.config-lock.json');
}

function delete_packages_directory()
{
    delete_recursive($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Packages');
}

function assert_pacakges_added_to_packages_directory($message)
{
    assert(
        file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Packages/Saeghe/simple-package')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Packages/Saeghe/simple-package/saeghe.config.json')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Packages/Saeghe/simple-package/README.md')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Packages/Saeghe/complex-package')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Packages/Saeghe/complex-package/saeghe.config.json')
        && file_exists($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/Packages/Saeghe/complex-package/saeghe.config-lock.json'),
        $message
    );
}

function assert_config_file_has_desired_data($message)
{
    $config = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/saeghe.config.json'), true, JSON_THROW_ON_ERROR);

    assert(
        assert(! isset($config['packages']['git@github.com:saeghe/simple-package.git']))

        && assert(isset($config['packages']['git@github.com:saeghe/complex-package.git']))
        && assert('development' === $config['packages']['git@github.com:saeghe/complex-package.git']),
        $message
    );
}

function assert_meta_file_has_desired_data($message)
{
    $meta = json_decode(file_get_contents($_SERVER['PWD'] . '/TestRequirements/Fixtures/ProjectWithTests/saeghe.config-lock.json'), true, JSON_THROW_ON_ERROR);

    assert(
        isset($meta['packages']['git@github.com:saeghe/simple-package.git'])
        && 'development' === $meta['packages']['git@github.com:saeghe/simple-package.git']['version']
        && 'saeghe' === $meta['packages']['git@github.com:saeghe/simple-package.git']['owner']
        && 'simple-package' === $meta['packages']['git@github.com:saeghe/simple-package.git']['repo']
        && '85f94d8c34cb5678a5b37707479517654645c102' === $meta['packages']['git@github.com:saeghe/simple-package.git']['hash']

        && isset($meta['packages']['git@github.com:saeghe/complex-package.git'])
        && 'development' === $meta['packages']['git@github.com:saeghe/complex-package.git']['version']
        && 'saeghe' === $meta['packages']['git@github.com:saeghe/complex-package.git']['owner']
        && 'complex-package' === $meta['packages']['git@github.com:saeghe/complex-package.git']['repo']
        && '08cccc569cffaf9ca67660d43e1de65b12895867' === $meta['packages']['git@github.com:saeghe/complex-package.git']['hash'],
        $message
    );
}
