<?php

namespace Saeghe\Saeghe\Commands\Initialize;

function run()
{
    global $projectRoot;
    global $config;
    global $meta;
    global $configFile;

    json_put($projectRoot . $configFile, $config);
    json_put(
        $projectRoot . str_replace('.json', '-lock.json', $configFile),
        $meta
    );
}
