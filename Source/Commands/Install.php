<?php

namespace Saeghe\Saeghe\Commands\Install;

use Saeghe\Saeghe\Config;
use Saeghe\Saeghe\Meta;
use Saeghe\Saeghe\Package;
use Saeghe\Saeghe\Project;
use Saeghe\Saeghe\FileManager\FileType\Json;
use function Saeghe\Cli\IO\Write\success;

function run(Project $project)
{
    $project->set_env_credentials();

    $config = Config::from_array(Json\to_array($project->config_file_path->to_string()));
    $meta = Meta::from_array(Json\to_array($project->config_lock_file_path->to_string()));

    array_walk(
        $meta->packages,
        fn (Package $package) => $package->download($package->root($project, $config)->to_string())
    );

    success('Packages has been installed successfully.');
}
