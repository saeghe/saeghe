<?php

namespace Saeghe\Saeghe\Commands\Install;

use Saeghe\Saeghe\Config;
use Saeghe\Saeghe\Meta;
use Saeghe\Saeghe\Package;
use Saeghe\Saeghe\Project;
use function Saeghe\Cli\IO\Write\success;

function run(Project $project)
{
    $config = Config::fromArray(json_to_array($project->configFilePath->toString()));
    $meta = Meta::fromArray(json_to_array($project->configLockFilePath->toString()));

    array_walk(
        $meta->packages,
        fn (Package $package) => $package->download($package->root($project, $config)->toString())
    );

    success('Packages has been installed successfully.');
}
