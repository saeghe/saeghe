<?php

namespace Saeghe\Saeghe\Commands\Flush;

use Saeghe\Saeghe\Project;
use function Saeghe\Cli\IO\Write\success;

function run(Project $project)
{
    $project->build_root->renew_recursive();
    $project->build_root->parent()->subdirectory('production')->renew_recursive();

    success('Build directory has been flushed.');
}
