<?php

namespace Saeghe\Saeghe\Commands\Initialize;

use function Saeghe\Cli\IO\Read\argument;

function run()
{
    global $projectRoot;
    global $config;
    global $meta;
    global $configFile;

    $packagesDirectory = argument('packages-directory');

    if ($packagesDirectory) {
        $config['packages-directory'] = $packagesDirectory;
    }

    file_put_contents(
        $projectRoot . $configFile,
        json_encode($config, JSON_PRETTY_PRINT) . PHP_EOL
    );

    file_put_contents(
        $projectRoot . str_replace('.json', '-lock.json', $configFile),
        json_encode($meta, JSON_PRETTY_PRINT) . PHP_EOL
    );
}
