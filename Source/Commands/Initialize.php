<?php

namespace Saeghe\Saeghe\Commands\Initialize;

function run()
{
    $filename = getopt('', ['config::'])['config'] ?? 'build.json';

    makeBuildJsonFile($filename);
}

function makeBuildJsonFile($filename)
{
    file_put_contents(
        $_SERVER['PWD'] . '/' . $filename,
        json_encode(['packages' => []], JSON_PRETTY_PRINT) . PHP_EOL
    );
}
