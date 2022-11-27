<?php

namespace Saeghe\Saeghe\Commands\Update;

use Saeghe\Saeghe\Config\Config;
use Saeghe\Saeghe\Package;
use Saeghe\Saeghe\Project;
use Saeghe\FileManager\FileType\Json;
use function Saeghe\Cli\IO\Read\parameter;
use function Saeghe\Cli\IO\Read\argument;
use function Saeghe\Cli\IO\Write\error;
use function Saeghe\Cli\IO\Write\line;
use function Saeghe\Saeghe\Commands\Add\add;
use function Saeghe\Saeghe\Commands\Remove\remove;
use function Saeghe\Cli\IO\Write\success;

function run(Project $project)
{
    $given_package_url = argument(2);
    $version = parameter('version');

    line('Updating package ' . $given_package_url . ' to ' . ($version ? 'version ' . $version : 'latest version') . '...');

    $package = Package::from_url($given_package_url);

    line('Setting env credential...');
    $project->set_env_credentials();

    line('Loading configs...');
    $config = Config::from_array(Json\to_array($project->config));

    line('Finding package in configs...');

    if (! $config->packages->has(fn (Package $installed_package) => $installed_package->is($package))) {
        error("Package $given_package_url does not found in your project!");
        return;
    }

    line('Setting package version...');
    $version ? $package->version($version) : $package->latest_version();

    line('Loading package\'s meta...');
    $package_url = $config->packages->first_key(fn (Package $installed_package) => $installed_package->is($package));

    line('Deleting package\'s files...');
    remove($project, $config, $package, $package_url);

    line('Detecting version hash...');
    $package->detect_hash();

    line('Downloading the package with new version...');
    add($project, $config, $package, $package_url);

    line('Updating configs...');
    $config->packages->put($package, $package_url);
    line('Committing new configs...');
    Json\write($project->config, $config->to_array());

    success("Package $given_package_url has been updated.");
}
