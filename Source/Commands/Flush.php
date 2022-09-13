<?php

namespace Saeghe\Saeghe\Commands\Flush;

function run()
{
    global $buildsPath;

    dir_clean($buildsPath . 'development');
    dir_clean($buildsPath . 'production');
}
