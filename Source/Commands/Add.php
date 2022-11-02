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
use function Saeghe\Saeghe\Providers\GitHub\github_token;
use const Saeghe\Saeghe\Providers\GitHub\GITHUB_DOMAIN;

function run(Project $project)
{
    $credential = json_to_array($project->credentials_path->to_string());
    github_token($credential[GITHUB_DOMAIN]['token'] ?? '');

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
    $package->detect_hash();

    if (! $package->is_downloaded($project, $config)) {
        $package->download($package->root($project, $config)->to_string());
    }

    $meta = Meta::from_array(json_to_array($project->config_lock_file_path->to_string()));
    $is_in_meta = array_reduce($meta->packages, fn ($carry, Package $installed_package) => $carry || $installed_package->is($package), false);

    if (! $is_in_meta) {
        $meta->packages[$package_url] = $package;
        json_put($project->config_lock_file_path->to_string(), $meta->to_array());
    }

    $package_config = Config::from_array(json_to_array($package->config_path($project, $config)->to_string()));

    foreach ($package_config->packages as $sub_package_url => $sub_package) {
        $is_sub_package_in_meta = array_reduce($meta->packages, fn ($carry, Package $installed_package) => $carry || $installed_package->is($sub_package), false);

        if (! $is_sub_package_in_meta) {
            add($project, $config, $sub_package, $sub_package_url);
        }
    }
}
