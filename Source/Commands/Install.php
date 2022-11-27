<?php

namespace Saeghe\Saeghe\Commands\Install;

use Saeghe\Saeghe\Config\Config;
use Saeghe\Saeghe\Config\Meta;
use Saeghe\Saeghe\Package;
use Saeghe\Saeghe\Project;
use Saeghe\FileManager\FileType\Json;
use function Saeghe\Cli\IO\Write\line;
use function Saeghe\Cli\IO\Write\success;

function run(Project $project)
{
    line('Installing packages...');
    line('Setting env credential...');
    $project->set_env_credentials();

    line('Loading configs...');
    $config = Config::from_array(Json\to_array($project->config));
    $meta = Meta::from_array(Json\to_array($project->config_lock));

    line('Downloading packages...');
    $meta->packages->each(function (Package $package, string $package_url) use ($project, $config) {
        $destination = $package->root($project, $config);
        line('Downloading package ' . $package_url . ' to ' . $destination);
        $package->download($destination);
    });

    success('Packages has been installed successfully.');
}
