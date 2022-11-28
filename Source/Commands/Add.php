<?php

namespace Saeghe\Saeghe\Commands\Add;

use Saeghe\Saeghe\Config\Config;
use Saeghe\Saeghe\Config\Meta;
use Saeghe\Saeghe\Package;
use Saeghe\Saeghe\Project;
use Saeghe\FileManager\FileType\Json;
use function Saeghe\Cli\IO\Read\parameter;
use function Saeghe\Cli\IO\Read\argument;
use function Saeghe\Cli\IO\Write\error;
use function Saeghe\Cli\IO\Write\line;
use function Saeghe\Cli\IO\Write\success;

function run(Project $project)
{
    $package_url = argument(2);
    $version = parameter('version');

    line('Adding package ' . $package_url . ($version ? ' version ' . $version : ' latest version') . '...');

    $package = Package::from_url($package_url);

    line('Setting env credential...');
    $project->set_env_credentials();

    line('Checking configs...');
    if (! $project->config->exists()) {
        error('Project is not initialized. Please try to initialize using the init command.');
        return;
    }

    $config = Config::from_array(Json\to_array($project->config));

    line('Checking installed packages...');
    if ($config->packages->has(fn (Package $installed_package) => $installed_package->is($package))) {
        error("Package $package_url is already exists.");
        return;
    }

    line('Setting package version...');
    $version ? $package->version($version) : $package->latest_version();

    line('Creating package directory...');
    $packages_directory = $project->root->subdirectory($config->packages_directory);
    unless($packages_directory->exists(), fn () => $packages_directory->make_recursive());

    line('Detecting version hash...');
    $package->detect_hash();

    line('Validating the package...');
    if (! $package->file_exists('saeghe.config.json')) {
        error("Given $package_url URL is not a Saeghe package.");

        return;
    }

    line('Downloading the package...');
    add($project, $config, $package, $package_url);

    line('Updating configs...');
    $config->packages->put($package, $package_url);

    line('Committing configs...');
    Json\write($project->config, $config->to_array());

    success("Package $package_url has been added successfully.");
}

function add(Project $project, Config $config, Package $package, $package_url)
{
    unless($package->is_downloaded($project, $config), fn () => $package->download($package->root($project, $config)));

    $meta = $project->config_lock->exists()
        ? Meta::from_array(Json\to_array($project->config_lock))
        : Meta::init();

    if (! $meta->packages->has(fn (Package $installed_package) => $installed_package->is($package))) {
        $meta->packages[$package_url] = $package;
        Json\write($project->config_lock, $meta->to_array());
    }

    $package_config = Config::from_array(Json\to_array($package->config_path($project, $config)));

    foreach ($package_config->packages as $sub_package_url => $sub_package) {
        if (! $meta->packages->has(fn (Package $installed_package) => $installed_package->is($sub_package))) {
            $sub_package->detect_hash();
            add($project, $config, $sub_package, $sub_package_url);
        }
    }
}
