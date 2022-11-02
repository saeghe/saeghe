<?php

namespace Saeghe\Saeghe\Commands\Remove;

use Saeghe\Saeghe\Config;
use Saeghe\Saeghe\Meta;
use Saeghe\Saeghe\Package;
use Saeghe\Saeghe\Project;
use Saeghe\Saeghe\FileManager\FileType\Json;
use function Saeghe\Cli\IO\Read\argument;
use function Saeghe\Cli\IO\Write\error;
use function Saeghe\Cli\IO\Write\success;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;

function run(Project $project)
{
    $given_package_url = argument(2);

    $config = Config::from_array(Json\to_array($project->config_file_path->to_string()));

    $package = array_reduce(
        $config->packages,
        function ($carry, Package $package) {
            return $package->is($carry) ? $package : $carry;
        },
        Package::from_url($given_package_url)
    );

    if (! isset($package->version)) {
        error("Package $given_package_url does not found in your project!");

        return;
    }

    $package_url = $given_package_url;

    foreach ($config->packages as $installed_package_url => $config_package) {
        if ($config_package->is($package)) {
            $package_url = $installed_package_url;
            break;
        }
    }

    remove($project, $config, $package, $package_url);

    unset($config->packages[$package_url]);
    json_put($project->config_file_path->to_string(), $config->to_array());

    success("Package $given_package_url has been removed successfully.");
}

function remove(Project $project, Config $config, Package $package, $package_url)
{
    $package_config = Config::from_array(Json\to_array($package->config_path($project, $config)->to_string()));

    foreach ($package_config->packages as $sub_package_url => $sub_package) {
        $sub_package_has_been_used = false;
        foreach ($config->packages as $used_packages) {
            $sub_package_has_been_used = $sub_package_has_been_used || $used_packages->is($sub_package);
        }

        if (! $sub_package_has_been_used) {
            remove($project, $config, $sub_package, $sub_package_url);
        }
    }

    delete_recursive($package->root($project, $config)->to_string());

    $meta = Meta::from_array(Json\to_array($project->config_lock_file_path->to_string()));

    unset($meta->packages[$package_url]);
    json_put($project->config_lock_file_path->to_string(), $meta->to_array());
}
