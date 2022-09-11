<?php

namespace Tests\MigrateCommandTest;

test(
    title: 'it should migrate symfony package',
    case: function () {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe --command=migrate --project=TestRequirements/Fixtures/composer-package");

        assert_correct_build_file('Build file is not correct!' . $output);
        assert_correct_build_lock_file('Build lock file is not correct!' . $output);
        assert_package_directory_content('Package directory content is not what we want!' . $output);
    },
    before: function () {
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/build.json');
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/build-lock.json');
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/Packages');
    },
    after: function () {
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/build.json');
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/build-lock.json');
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/Packages');
    },
);

function assert_correct_build_file($message)
{
    $root = $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/';
    $stub = $_SERVER['PWD'] . '/TestRequirements/Stubs/composer-package/';

    assert(
        file_exists($root . 'build.json')
        && file_get_contents($root . 'build.json') === file_get_contents($stub . 'build.json.stub'),
        $message
    );
}

function assert_correct_build_lock_file($message)
{
    $root = $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/';
    $stub = $_SERVER['PWD'] . '/TestRequirements/Stubs/composer-package/';

    assert(
        file_exists($root . 'build-lock.json')
        && file_get_contents($root . 'build-lock.json') === file_get_contents($stub . 'build-lock.json.stub'),
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
        && file_exists($root . 'Packages/Seldaek/monolog/build.json')
        && file_exists($root . 'Packages/Seldaek/monolog/build-lock.json')
        && file_exists($root . 'Packages/php-fig')
        && file_exists($root . 'Packages/php-fig/log')
        && file_exists($root . 'Packages/php-fig/log/composer.json')
        && file_exists($root . 'Packages/php-fig/log/build.json')
        && file_exists($root . 'Packages/php-fig/log/build-lock.json')
        && file_get_contents($root . 'Packages/Seldaek/monolog/build.json') === file_get_contents($stub . 'monolog-build.json.stub')
        && file_get_contents($root . 'Packages/Seldaek/monolog/build-lock.json') === file_get_contents($stub . 'monolog-build-lock.json.stub')
        && file_get_contents($root . 'Packages/php-fig/log/build.json') === file_get_contents($stub . 'log-build.json.stub')
        && file_get_contents($root . 'Packages/php-fig/log/build-lock.json') === file_get_contents($stub . 'log-build-lock.json.stub')
        ,
        $message
    );
}
