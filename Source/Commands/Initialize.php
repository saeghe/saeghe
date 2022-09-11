<?php

namespace Saeghe\Saeghe\Commands\Initialize;

use function Saeghe\Cli\IO\Read\argument;

function run()
{
    global $projectRoot;
    global $setting;
    global $lockSetting;
    global $config;

    $packagesDirectory = argument('packages-directory');

    if ($packagesDirectory) {
        $setting['packages-directory'] = $packagesDirectory;
    }

    file_put_contents(
        $projectRoot . $config,
        json_encode($setting, JSON_PRETTY_PRINT) . PHP_EOL
    );

    file_put_contents(
        $projectRoot . str_replace('.json', '-lock.json', $config),
        json_encode($lockSetting, JSON_PRETTY_PRINT) . PHP_EOL
    );
}
