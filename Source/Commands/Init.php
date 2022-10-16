<?php

namespace Saeghe\Saeghe\Commands\Init;

use function Saeghe\Cli\IO\Write\success;

function run()
{
    global $config;
    global $meta;
    global $configPath;
    global $metaFilePath;

    json_put($configPath, $config);
    json_put($metaFilePath, $meta);

    success('Project has been initialized.');
}
