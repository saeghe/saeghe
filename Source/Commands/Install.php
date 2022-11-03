<?php

namespace Saeghe\Saeghe\Commands\Install;

use Saeghe\Saeghe\Config;
use Saeghe\Saeghe\Meta;
use Saeghe\Saeghe\Package;
use Saeghe\Saeghe\Project;
use Saeghe\Saeghe\FileManager\FileType\Json;
use function Saeghe\Cli\IO\Write\error;
use function Saeghe\Cli\IO\Write\success;
use function Saeghe\Saeghe\Providers\GitHub\github_token;
use const Saeghe\Saeghe\Providers\GitHub\GITHUB_DOMAIN;

function run(Project $project)
{
    if (! $project->credentials_path->exists()) {
        error('There is no credential file. Please use the `credential` command to add your token.');

        return;
    }

    $credential = Json\to_array($project->credentials_path->to_string());
    github_token($credential[GITHUB_DOMAIN]['token'] ?? '');

    $config = Config::from_array(Json\to_array($project->config_file_path->to_string()));
    $meta = Meta::from_array(Json\to_array($project->config_lock_file_path->to_string()));

    array_walk(
        $meta->packages,
        fn (Package $package) => $package->download($package->root($project, $config)->to_string())
    );

    success('Packages has been installed successfully.');
}
