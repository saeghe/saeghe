<?php

namespace Saeghe\Saeghe\Commands\Install;

use Saeghe\Saeghe\Config;
use Saeghe\Saeghe\Meta;
use Saeghe\Saeghe\Package;
use Saeghe\Saeghe\Project;
use function Saeghe\Cli\IO\Write\success;
use function Saeghe\Saeghe\Providers\GitHub\github_token;
use const Saeghe\Saeghe\Providers\GitHub\GITHUB_DOMAIN;

function run(Project $project)
{
    $credential = json_to_array($project->credentials_path->to_string());
    github_token($credential[GITHUB_DOMAIN]['token'] ?? '');

    $config = Config::from_array(json_to_array($project->config_file_path->to_string()));
    $meta = Meta::from_array(json_to_array($project->config_lock_file_path->to_string()));

    array_walk(
        $meta->packages,
        fn (Package $package) => $package->download($package->root($project, $config)->to_string())
    );

    success('Packages has been installed successfully.');
}
