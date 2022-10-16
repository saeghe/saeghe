<?php

namespace Saeghe\Saeghe\Commands\Flush;

use function Saeghe\Cli\IO\Write\success;

function run()
{
    global $buildsPath;

    dir_clean($buildsPath . 'development');
    dir_clean($buildsPath . 'production');

    success('Build directory has been flushed.');
}
