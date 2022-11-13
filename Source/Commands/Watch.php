<?php

namespace Saeghe\Saeghe\Commands\Watch;

use Saeghe\Saeghe\Project;
use function Saeghe\Cli\IO\Read\parameter;
use function Saeghe\FileManager\Resolver\root;

function run(Project $project)
{
    $seconds = (int) parameter('wait', 3);

    $saeghe_path = root() . 'saeghe';
    chdir($project->root);

    while (true) {
        echo shell_exec("php $saeghe_path build");

        sleep($seconds);
    }
}
