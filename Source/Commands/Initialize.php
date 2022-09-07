<?php

namespace Saeghe\Saeghe\Commands\Initialize;

use function Saeghe\Cli\IO\Read\argument;

function run()
{
    global $projectRoot;

    $filename = argument('config', 'build.json');
    $packagesDirectory = argument('packages-directory');

    $config = ['packages' => []];

    if ($packagesDirectory) {
        $config['packages-directory'] = $packagesDirectory;
    }

    file_put_contents(
        $projectRoot . $filename,
        json_encode($config, JSON_PRETTY_PRINT) . PHP_EOL
    );
}
