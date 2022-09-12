<?php

namespace Tests\MigrateCommandTest;

test(
    title: 'it should migrate symfony package',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=migrate --project=TestRequirements/Fixtures/composer-package");

        assert_correct_config_file('Config file is not correct!' . $output);
        assert_correct_meta_file('Meta file data is not correct!' . $output);
        assert_package_directory_content('Package directory content is not what we want!' . $output);
    },
    before: function () {
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/saeghe.config.json');
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/saeghe.config-lock.json');
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/Packages');
    },
    after: function () {
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/saeghe.config.json');
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/saeghe.config-lock.json');
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/Packages');
    },
);

function assert_correct_config_file($message)
{
    $root = $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/';
    $stub = $_SERVER['PWD'] . '/TestRequirements/Stubs/composer-package/';

    assert(
        file_exists($root . 'saeghe.config.json')
        && file_get_contents($root . 'saeghe.config.json') === file_get_contents($stub . 'saeghe.config.json.stub'),
        $message
    );
}

function assert_correct_meta_file($message)
{
    $root = $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/';
    $stub = $_SERVER['PWD'] . '/TestRequirements/Stubs/composer-package/';

    assert(
        file_exists($root . 'saeghe.config-lock.json')
        && file_get_contents($root . 'saeghe.config-lock.json') === file_get_contents($stub . 'saeghe.config-lock.json.stub'),
        $message
    );
}

function assert_package_directory_content($message)
{
    $root = $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/';
    $stub = $_SERVER['PWD'] . '/TestRequirements/Stubs/composer-package/';

    assert(
        file_exists($root . 'Packages')
        && file_exists($root . 'Packages/Seldaek')
        && file_exists($root . 'Packages/Seldaek/monolog')
        && file_exists($root . 'Packages/Seldaek/monolog/composer.json')
        && file_exists($root . 'Packages/Seldaek/monolog/saeghe.config.json')
        && file_exists($root . 'Packages/Seldaek/monolog/saeghe.config-lock.json')
        && file_exists($root . 'Packages/php-fig')
        && file_exists($root . 'Packages/php-fig/log')
        && file_exists($root . 'Packages/php-fig/log/composer.json')
        && file_exists($root . 'Packages/php-fig/log/saeghe.config.json')
        && file_exists($root . 'Packages/php-fig/log/saeghe.config-lock.json')
        && file_get_contents($root . 'Packages/Seldaek/monolog/saeghe.config.json') === file_get_contents($stub . 'monolog-saeghe.config.json.stub')
        && file_get_contents($root . 'Packages/Seldaek/monolog/saeghe.config-lock.json') === file_get_contents($stub . 'monolog-saeghe.config-lock.json.stub')
        && file_get_contents($root . 'Packages/php-fig/log/saeghe.config.json') === file_get_contents($stub . 'log-saeghe.config.json.stub')
        && file_get_contents($root . 'Packages/php-fig/log/saeghe.config-lock.json') === file_get_contents($stub . 'log-saeghe.config-lock.json.stub')
        ,
        $message
    );
}
