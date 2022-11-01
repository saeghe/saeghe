<?php

namespace Tests\System\MigrateCommand\MigrateCommandWithoutProjectTest;

use Saeghe\Cli\IO\Write;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\Saeghe\FileManager\Path\realpath;

test(
    title: 'it should show proper message when there is no composer.json file',
    case: function ($project) {
        $output = shell_exec($_SERVER['PWD'] . "/saeghe migrate --project=TestRequirements/" . $project);

        Write\assert_error('There is no composer.json file in this project!', $output);

        return $project;
    },
    before: function () {
        $project = 'EmptyComposerProject';

        mkdir(realpath($_SERVER['PWD'] . '/TestRequirements/' . $project));

        return $project;
    },
    after: function ($project) {
        delete_recursive(realpath($_SERVER['PWD'] . '/TestRequirements/' . $project));
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

        mkdir(realpath($_SERVER['PWD'] . '/TestRequirements/' . $project));
        copy(
            realpath($_SERVER['PWD'] . '/TestRequirements/Fixtures/composer-package/composer.json'),
            realpath($_SERVER['PWD'] . '/TestRequirements/EmptyComposerProject/composer.json')
        );

        return $project;
    },
    after: function ($project) {
        delete_recursive(realpath($_SERVER['PWD'] . '/TestRequirements/' . $project));
    },
);
