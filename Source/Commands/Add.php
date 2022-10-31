<?php

namespace Saeghe\Saeghe\Commands\Add;

use Saeghe\Saeghe\Config;
use Saeghe\Saeghe\Meta;
use Saeghe\Saeghe\Package;
use Saeghe\Saeghe\Project;
use function Saeghe\Cli\IO\Read\parameter;
use function Saeghe\Cli\IO\Read\argument;
use function Saeghe\Cli\IO\Write\error;
use function Saeghe\Cli\IO\Write\success;

function run(Project $project)
{
    $package_url = argument(2);
    $version = parameter('version');

    if (! file_exists($project->config_file_path->to_string())) {
        error('Project is not initialized. Please try to initialize using the init command.');
        return;
    }

    $config = Config::from_array(json_to_array($project->config_file_path->to_string()));

    $package = array_reduce(
        $config->packages,
        function ($carry, Package $package) {
            return $package->is($carry) ? $package : $carry;
        },
        Package::from_url($package_url)
    );

    if (isset($package->version)) {
        error("Package $package_url is already exists");
        return;
    }

    $version ? $package->version($version) : $package->latest_version();

    if (! file_exists($project->root->append($config->packages_directory)->to_string())) {
        dir_make_recursive($project->root->append($config->packages_directory)->to_string());
    }

    add($project, $config, $package, $package_url);

    $config->packages[$package_url] = $package;
    json_put($project->config_file_path->to_string(), $config->to_array());

    success("Package $package_url has been added successfully.");
}

function add(Project $project, Config $config, Package $package, $package_url)
{
    $package->detect_hash()->download($package->root($project, $config)->to_string());

    $meta = Meta::from_array(json_to_array($project->config_lock_file_path->to_string()));
    $meta->packages[$package_url] = $package;
    json_put($project->config_lock_file_path->to_string(), $meta->to_array());

    $package_config = Config::from_array(json_to_array($package->config_path($project, $config)->to_string()));

    foreach ($package_config->packages as $sub_package_url => $sub_package) {
        $is_sub_package_exists = false;
        foreach ($meta->packages as $installed_package_url => $installed_package) {
            if ($installed_package->is($sub_package)) {
                $is_sub_package_exists = true;
                break;
            }
        }

        if (! $is_sub_package_exists) {
            add($project, $config, $sub_package, $sub_package_url);
        }
    }
}
