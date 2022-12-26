<?php

namespace Saeghe\Saeghe\Commands\Version;

use Saeghe\Cli\IO\Write;

function run(): void
{
    Write\success('Saeghe version 1.15.1');
}
