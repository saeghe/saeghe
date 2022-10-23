<?php

namespace Tests\MigrateCommand\MigrateCommandWithoutProjectTest;

use Saeghe\Cli\IO\Write;

test(
    title: 'it should show proper message when there is no composer.json file',
    case: function ($project) {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe migrate --project=TestRequirements/" . $project);

        Write\assert_error('There is no composer.json file in this project!', $output);

        return $project;
    },
    before: function () {
        $project = 'EmptyComposerProject';

        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/' . $project);
        shell_exec('mkdir ' . $_SERVER['PWD'] . '/TestRequirements/' . $project);

        return $project;
    },
    after: function ($project) {
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/' . $project);
    },
);

test(
    title: 'it should show proper message when there is no composer.lock file',
    case: function ($project) {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe migrate --project=TestRequirements/" . $project);

        Write\assert_error('There is no composer.lock file in this project!', $output);

        return $project;
    },
    before: function () {
        $project = 'EmptyComposerProject';

        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/' . $project);
        shell_exec('mkdir ' . $_SERVER['PWD'] . '/TestRequirements/' . $project);
        shell_exec('cp '
            . $_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/composer.json '
            . $_SERVER['PWD'] . '/TestRequirements/EmptyComposerProject/composer.json '
        );

        return $project;
    },
    after: function ($project) {
        shell_exec('rm -fR ' . $_SERVER['PWD'] . '/TestRequirements/' . $project);
    },
);
