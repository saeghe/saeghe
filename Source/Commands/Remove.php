<?php

namespace Saeghe\Saeghe\Commands\Remove;

use Saeghe\Saeghe\Config\Config;
use Saeghe\Saeghe\Config\Meta;
use Saeghe\Saeghe\Package;
use Saeghe\Saeghe\Project;
use Saeghe\FileManager\FileType\Json;
use function Saeghe\Cli\IO\Read\argument;
use function Saeghe\Cli\IO\Write\error;
use function Saeghe\Cli\IO\Write\line;
use function Saeghe\Cli\IO\Write\success;

function run(Project $project)
{
    $given_package_url = argument(2);

    line('Removing package ' . $given_package_url);

    $package = Package::from_url($given_package_url);

    line('Loading configs...');
    $config = Config::from_array(Json\to_array($project->config));

    line('Finding package in configs...');

    if (! $config->packages->has(fn (Package $installed_package) => $installed_package->is($package))) {
        error("Package $given_package_url does not found in your project!");
        return;
    }

    line('Loading package\'s meta...');
    foreach ($config->packages as $installed_package_url => $config_package) {
        if ($config_package->is($package)) {
            $package_url = $installed_package_url;
            break;
        }
    }

    line('Deleting package\'s files...');
    remove($project, $config, $package, $package_url);

    line('Removing package from config...');
    $config->packages->forget($package_url);
    line('Committing configs...');
    Json\write($project->config, $config->to_array());

    success("Package $given_package_url has been removed successfully.");
}

function remove(Project $project, Config $config, Package $package, $package_url)
{
    $package_config = Config::from_array(Json\to_array($package->config_path($project, $config)));

    foreach ($package_config->packages as $sub_package_url => $sub_package) {
        $sub_package_has_been_used = false;
        foreach ($config->packages as $used_packages) {
            $sub_package_has_been_used = $sub_package_has_been_used || $used_packages->is($sub_package);
        }

        if (! $sub_package_has_been_used) {
            remove($project, $config, $sub_package, $sub_package_url);
        }
    }

    $package->root($project, $config)->delete_recursive();

    $meta = Meta::from_array(Json\to_array($project->config_lock));

    unset($meta->packages[$package_url]);
    Json\write($project->config_lock, $meta->to_array());
}
