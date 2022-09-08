<?php

namespace Saeghe\Saeghe\Commands\Update;

use function Saeghe\Cli\IO\Read\argument;
use function Saeghe\Saeghe\Commands\Add\add;
use function Saeghe\Saeghe\Commands\Remove\remove;

function run()
{
    $package = argument('package');

    global $setting;
    global $lockSetting;
    global $packagesDirectory;

    remove($package, $setting, $lockSetting, $packagesDirectory);
    add($packagesDirectory, $package, null);
}
