<?php

namespace Saeghe\Saeghe\Commands\Flush;

use Saeghe\Saeghe\Classes\Build\Build;
use Saeghe\Saeghe\Classes\Environment\Environment;
use Saeghe\Saeghe\Classes\Project\Project;
use function Saeghe\Cli\IO\Read\parameter;
use function Saeghe\Cli\IO\Write\success;

function run(Environment $environment): void
{
    $project = new Project($environment->pwd->subdirectory(parameter('project', '')));

    $development_build = new Build($project, 'development');
    $production_build = new Build($project, 'production');

    $development_build->root()->renew_recursive();
    $production_build->root()->renew_recursive();

    success('Build directory has been flushed.');
}
