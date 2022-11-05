<?php

namespace Saeghe\Saeghe\Commands\Flush;

use Saeghe\Saeghe\Project;
use function Saeghe\Cli\IO\Write\success;
use function Saeghe\Saeghe\FileManager\Directory\renew_recursive;

function run(Project $project)
{
    renew_recursive($project->build_root->directory());
    renew_recursive($project->build_root->parent()->append('production')->directory());

    success('Build directory has been flushed.');
}
