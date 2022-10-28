<?php

namespace Saeghe\Saeghe\Commands\Flush;

use Saeghe\Saeghe\Project;
use function Saeghe\Cli\IO\Write\success;

function run(Project $project)
{
    dir_clean($project->buildRoot->directory());
    dir_clean($project->buildRoot->parent()->append('production')->directory());

    success('Build directory has been flushed.');
}
