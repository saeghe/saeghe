<?php

namespace Saeghe\Saeghe\Commands\Watch;

use Saeghe\Saeghe\Project;
use function Saeghe\Cli\IO\Read\parameter;

function run(Project $project)
{
    global $argv;

    $seconds = (int) parameter('wait', 3);
    $project = parameter('project');
    $command = "php $argv[0] build";
    $command = $project ? $command . ' --project=' . $project : $command;

    while (true) {
        echo shell_exec($command);

        sleep($seconds);
    }
}
