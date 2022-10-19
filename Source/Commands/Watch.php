<?php

namespace Saeghe\Saeghe\Commands\Watch;

use function Saeghe\Cli\IO\Read\parameter;

function run()
{
    global $projectRoot;

    $seconds = (int) parameter('wait', 3);

    chdir($projectRoot);

    while (true) {
        echo shell_exec('saeghe --command=build');

        sleep($seconds);
    }
}
