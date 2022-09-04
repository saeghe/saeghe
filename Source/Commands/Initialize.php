<?php

namespace Saeghe\Saeghe\Commands\Initialize;

function run()
{
    global $projectRoot;

    $filename = getopt('', ['config::'])['config'] ?? 'build.json';
    $packagesDirectory = getopt('', ['packages-directory::'])['packages-directory'] ?? null;

    $config = ['packages' => []];

    if ($packagesDirectory) {
        $config['packages-directory'] = $packagesDirectory;
    }

    file_put_contents(
        $projectRoot . $filename,
        json_encode($config, JSON_PRETTY_PRINT) . PHP_EOL
    );
}
