<?php

namespace Saeghe\Saeghe\Commands\Watch;

use function Saeghe\Cli\IO\Read\argument;

function run()
{
    global $projectRoot;

    $seconds = (int) argument('wait', 3);

    chdir($projectRoot);

    while (true) {
        echo shell_exec('saeghe --command=build');

        sleep($seconds);
    }
}
