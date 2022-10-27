<?php

namespace Saeghe\Saeghe\Commands\Flush;

use Saeghe\Saeghe\Project;
use function Saeghe\Cli\IO\Write\success;

function run(Project $project)
{
    dir_clean($project->buildRoot);
    dir_clean(dirname($project->buildRoot) . '/production');

    success('Build directory has been flushed.');
}
