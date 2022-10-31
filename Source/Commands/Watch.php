<?php

namespace Saeghe\Saeghe\Commands\Watch;

use Saeghe\Saeghe\Project;
use function Saeghe\Cli\IO\Read\parameter;

function run(Project $project)
{
    $seconds = (int) parameter('wait', 3);

    chdir($project->root->to_string());

    while (true) {
        echo shell_exec('saeghe --command=build');

        sleep($seconds);
    }
}
