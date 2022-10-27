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
    $packageUrl = argument(2);
    $version = parameter('version');

    if (! file_exists($project->configFilePath)) {
        error('Project is not initialized. Please try to initialize using the init command.');
        return;
    }

    $config = Config::fromArray(json_to_array($project->configFilePath));

    $package = array_reduce(
        $config->packages,
        function ($carry, Package $package) {
            return $package->is($carry) ? $package : $carry;
        },
        Package::fromUrl($packageUrl)
    );

    if (isset($package->version)) {
        error("Package $packageUrl is already exists");
        return;
    }

    $version ? $package->version($version) : $package->latestVersion();

    if (! file_exists($project->root . $config->packagesDirectory)) {
        dir_make_recursive($project->root . $config->packagesDirectory);
    }

    add($project, $config, $package, $packageUrl);

    $config->packages[$packageUrl] = $package;
    json_put($project->configFilePath, $config->toArray());

    success("Package $packageUrl has been added successfully.");
}

function add(Project $project, Config $config, Package $package, $packageUrl)
{
    $package->detectHash()->download($package->root($project, $config));

    $meta = Meta::fromArray(json_to_array($project->configLockFilePath));
    $meta->packages[$packageUrl] = $package;
    json_put($project->configLockFilePath, $meta->toArray());

    $packageConfig = Config::fromArray(json_to_array($package->configPath($project, $config)));

    foreach ($packageConfig->packages as $packageUrl => $package) {
        add($project, $config, $package, $packageUrl);
    }
}
