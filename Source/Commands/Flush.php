<?php

namespace Saeghe\Saeghe\Commands\Flush;

use Saeghe\Saeghe\Project;
use function Saeghe\Cli\IO\Write\success;

function run(Project $project)
{
    dir_clean($project->build_root->directory());
    dir_clean($project->build_root->parent()->append('production')->directory());

    success('Build directory has been flushed.');
}
