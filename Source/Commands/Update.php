<?php

namespace Saeghe\Saeghe\Commands\Update;

use function Saeghe\Cli\IO\Read\parameter;
use function Saeghe\Cli\IO\Read\argument;
use function Saeghe\Saeghe\Commands\Add\add;
use function Saeghe\Saeghe\Commands\Remove\remove;
use function Saeghe\Cli\IO\Write\success;

function run()
{
    global $projectRoot;
    global $config;
    global $meta;
    global $packagesDirectory;

    $package = argument(2);
    $version = parameter('version');

    remove($package, $config, $meta, $packagesDirectory);
    $packageMeta = add($packagesDirectory, $package, $version);

    $config['packages'][$package] = $packageMeta['version'];

    $configFile = $projectRoot . DEFAULT_CONFIG_FILENAME;
    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));

    success("Package $package has been updated.");
}
