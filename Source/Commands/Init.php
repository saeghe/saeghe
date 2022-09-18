<?php

namespace Saeghe\Saeghe\Commands\Init;

function run()
{
    global $config;
    global $meta;
    global $configPath;
    global $metaFilePath;

    json_put($configPath, $config);
    json_put($metaFilePath, $meta);
}
