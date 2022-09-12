<?php

namespace Saeghe\Saeghe\Commands\Update;

use function Saeghe\Cli\IO\Read\argument;
use function Saeghe\Saeghe\Commands\Add\add;
use function Saeghe\Saeghe\Commands\Remove\remove;

function run()
{
    $package = argument('package');

    global $config;
    global $meta;
    global $packagesDirectory;

    remove($package, $config, $meta, $packagesDirectory);
    add($packagesDirectory, $package, null);
}
