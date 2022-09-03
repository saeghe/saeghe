<?php

namespace Saeghe\Saeghe\Commands\Initialize;

function run()
{
    global $projectRoot;
    $filename = getopt('', ['config::'])['config'] ?? 'build.json';

    makeBuildJsonFile($projectRoot . $filename);
}

function makeBuildJsonFile($filepath)
{
    file_put_contents(
        $filepath,
        json_encode(['packages' => []], JSON_PRETTY_PRINT) . PHP_EOL
    );
}
