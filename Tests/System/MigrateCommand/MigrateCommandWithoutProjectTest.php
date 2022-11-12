<?php

namespace Tests\System\MigrateCommand\MigrateCommandWithoutProjectTest;

use Saeghe\Cli\IO\Write;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\Saeghe\FileManager\Resolver\realpath;

test(
    title: 'it should show proper message when there is no composer.json file',
    case: function ($project) {
        $output = shell_exec('php ' . root() . 'saeghe migrate --project=TestRequirements/' . $project);

        Write\assert_error('There is no composer.json file in this project!', $output);

        return $project;
    },
    before: function () {
        $project = 'EmptyComposerProject';

        mkdir(realpath(root() . 'TestRequirements/' . $project));

        return $project;
    },
    after: function ($project) {
        delete_recursive(realpath(root() . 'TestRequirements/' . $project));
    },
);

test(
    title: 'it should show proper message when there is no composer.lock file',
    case: function ($project) {
        $output = shell_exec('php ' . root() . 'saeghe migrate --project=TestRequirements/' . $project);

        Write\assert_error('There is no composer.lock file in this project!', $output);

        return $project;
    },
    before: function () {
        $project = 'EmptyComposerProject';

        mkdir(realpath(root() . 'TestRequirements/' . $project));
        copy(
            realpath(root() . 'TestRequirements/Fixtures/composer-package/composer.json'),
            realpath(root() . 'TestRequirements/EmptyComposerProject/composer.json')
        );

        return $project;
    },
    after: function ($project) {
        delete_recursive(realpath(root() . 'TestRequirements/' . $project));
    },
);
