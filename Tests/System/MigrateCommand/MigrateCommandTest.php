<?php

namespace Tests\System\MigrateCommand\MigrateCommandTest;

use Saeghe\Cli\IO\Write;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\Saeghe\FileManager\File\delete;
use function Saeghe\Saeghe\FileManager\Path\realpath;

test(
    title: 'it should migrate symfony package',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe migrate --project=TestRequirements/Fixtures/composer-package");

        assert_correct_config_file('Config file is not correct!' . $output);
        assert_correct_meta_file('Meta file data is not correct!' . $output);
        assert_package_directory_content('Package directory content is not what we want!' . $output);
        Write\assert_success('Project migrated successfully.', $output);
    },
    after: function () {
        delete(realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/saeghe.config.json'));
        delete(realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/saeghe.config-lock.json'));
        delete_recursive(realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/Packages'));
    },
);

function assert_correct_config_file($message)
{
    $root = $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/';
    $stub = $_SERVER['PWD'] . '/TestRequirements/Stubs/composer-package/';

    assert(
        file_exists(realpath($root . 'saeghe.config.json'))
        && file_get_contents(realpath($root . 'saeghe.config.json')) === file_get_contents(realpath($stub . 'saeghe.config.json.stub')),
        $message
    );
}

function assert_correct_meta_file($message)
{
    $root = $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/';
    $stub = $_SERVER['PWD'] . '/TestRequirements/Stubs/composer-package/';

    assert(
        file_exists(realpath($root . 'saeghe.config-lock.json'))
        && file_get_contents(realpath($root . 'saeghe.config-lock.json')) === file_get_contents(realpath($stub . 'saeghe.config-lock.json.stub')),
        $message
    );
}

function assert_package_directory_content($message)
{
    $root = $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/';
    $stub = $_SERVER['PWD'] . '/TestRequirements/Stubs/composer-package/';

    assert(
        file_exists(realpath($root . 'Packages'))
        && file_exists(realpath($root . 'Packages/Seldaek'))
        && file_exists(realpath($root . 'Packages/Seldaek/monolog'))
        && file_exists(realpath($root . 'Packages/Seldaek/monolog/composer.json'))
        && file_exists(realpath($root . 'Packages/Seldaek/monolog/saeghe.config.json'))
        && file_exists(realpath($root . 'Packages/Seldaek/monolog/saeghe.config-lock.json'))
        && file_exists(realpath($root . 'Packages/php-fig'))
        && file_exists(realpath($root . 'Packages/php-fig/log'))
        && file_exists(realpath($root . 'Packages/php-fig/log/composer.json'))
        && file_exists(realpath($root . 'Packages/php-fig/log/saeghe.config.json'))
        && file_exists(realpath($root . 'Packages/php-fig/log/saeghe.config-lock.json'))
        && file_exists(realpath($root . 'Packages/symfony'))
        && file_exists(realpath($root . 'Packages/symfony/thanks'))
        && file_exists(realpath($root . 'Packages/symfony/thanks/composer.json'))
        && file_exists(realpath($root . 'Packages/symfony/thanks/saeghe.config.json'))
        && file_exists(realpath($root . 'Packages/symfony/thanks/saeghe.config-lock.json'))
        && file_get_contents(realpath($root . 'Packages/Seldaek/monolog/saeghe.config.json')) === file_get_contents(realpath($stub . 'monolog-saeghe.config.json.stub'))
        && file_get_contents(realpath($root . 'Packages/Seldaek/monolog/saeghe.config-lock.json')) === file_get_contents(realpath($stub . 'monolog-saeghe.config-lock.json.stub'))
        && file_get_contents(realpath($root . 'Packages/php-fig/log/saeghe.config.json')) === file_get_contents(realpath($stub . 'log-saeghe.config.json.stub'))
        && file_get_contents(realpath($root . 'Packages/php-fig/log/saeghe.config-lock.json')) === file_get_contents(realpath($stub . 'log-saeghe.config-lock.json.stub'))
        && file_get_contents(realpath($root . 'Packages/symfony/thanks/saeghe.config.json')) === file_get_contents(realpath($stub . 'symfony-thanks-saeghe.config.json.stub'))
        && file_get_contents(realpath($root . 'Packages/symfony/thanks/saeghe.config-lock.json')) === file_get_contents(realpath($stub . 'symfony-thanks-saeghe.config-lock.json.stub'))
        ,
        $message
    );
}
