<?php

namespace Saeghe\Saeghe\Commands\Update;

use Saeghe\Saeghe\Config;
use Saeghe\Saeghe\Package;
use Saeghe\Saeghe\Project;
use function Saeghe\Cli\IO\Read\parameter;
use function Saeghe\Cli\IO\Read\argument;
use function Saeghe\Cli\IO\Write\error;
use function Saeghe\Saeghe\Commands\Add\add;
use function Saeghe\Saeghe\Commands\Remove\remove;
use function Saeghe\Cli\IO\Write\success;
use function Saeghe\Saeghe\Providers\GitHub\github_token;
use const Saeghe\Saeghe\Providers\GitHub\GITHUB_DOMAIN;

function run(Project $project)
{
    $credential = json_to_array($project->credentials_path->to_string());
    github_token($credential[GITHUB_DOMAIN]['token'] ?? '');

    $given_package_url = argument(2);
    $version = parameter('version');

    $config = Config::from_array(json_to_array($project->config_file_path->to_string()));

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

    $version ? $package->version($version) : $package->latest_version();

    $package_url = $given_package_url;

    foreach ($config->packages as $installed_package_url => $config_package) {
        if ($config_package->is($package)) {
            $package_url = $installed_package_url;
            break;
        }
    }

    remove($project, $config, $package, $package_url);
    add($project, $config, $package, $package_url);

    $config->packages[$package_url] = $package;
    json_put($project->config_file_path->to_string(), $config->to_array());

    success("Package $given_package_url has been updated.");
}
