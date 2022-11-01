<?php

namespace Saeghe\Saeghe\Commands\Version;

use Saeghe\Cli\IO\Write;
use Saeghe\Saeghe\Project;

function run(Project $project)
{
    Write\success('Saeghe version 1.3.0');
}
