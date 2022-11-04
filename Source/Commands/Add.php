<?php

namespace Saeghe\Saeghe\Commands\Add;

use Saeghe\Saeghe\Config;
use Saeghe\Saeghe\Meta;
use Saeghe\Saeghe\Package;
use Saeghe\Saeghe\Project;
use Saeghe\Saeghe\FileManager\File;
use Saeghe\Saeghe\FileManager\FileType\Json;
use function Saeghe\Cli\IO\Read\parameter;
use function Saeghe\Cli\IO\Read\argument;
use function Saeghe\Cli\IO\Write\error;
use function Saeghe\Cli\IO\Write\success;

function run(Project $project)
{
    $project->set_env_credentials();

    $package_url = argument(2);
    $version = parameter('version');

    if (! file_exists($project->config_file_path->to_string())) {
        error('Project is not initialized. Please try to initialize using the init command.');
        return;
    }

    $config = Config::from_array(Json\to_array($project->config_file_path->to_string()));

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

    $package->detect_hash();
    if (! $package->file_exists('saeghe.config.json')) {
        error("Given $package_url URL is not a Saeghe package.");

        return;
    }

    add($project, $config, $package, $package_url);

    $config->packages[$package_url] = $package;
    json_put($project->config_file_path->to_string(), $config->to_array());

    success("Package $package_url has been added successfully.");
}

function add(Project $project, Config $config, Package $package, $package_url)
{
    if (! $package->is_downloaded($project, $config)) {
        $package->download($package->root($project, $config)->to_string());
    }

    $meta = File\exists($project->config_lock_file_path->to_string())
        ? Meta::from_array(Json\to_array($project->config_lock_file_path->to_string()))
        : Meta::init();

    $is_in_meta = array_reduce($meta->packages, fn ($carry, Package $installed_package) => $carry || $installed_package->is($package), false);

    if (! $is_in_meta) {
        $meta->packages[$package_url] = $package;
        json_put($project->config_lock_file_path->to_string(), $meta->to_array());
    }

    $package_config = Config::from_array(Json\to_array($package->config_path($project, $config)->to_string()));

    foreach ($package_config->packages as $sub_package_url => $sub_package) {
        $is_sub_package_in_meta = array_reduce($meta->packages, fn ($carry, Package $installed_package) => $carry || $installed_package->is($sub_package), false);

        if (! $is_sub_package_in_meta) {
            $sub_package->detect_hash();
            add($project, $config, $sub_package, $sub_package_url);
        }
    }
}
