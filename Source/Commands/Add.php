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

    if (! file_exists($project->configFilePath->toString())) {
        error('Project is not initialized. Please try to initialize using the init command.');
        return;
    }

    $config = Config::fromArray(json_to_array($project->configFilePath->toString()));

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

    if (! file_exists($project->root->append($config->packagesDirectory)->toString())) {
        dir_make_recursive($project->root->append($config->packagesDirectory)->toString());
    }

    add($project, $config, $package, $packageUrl);

    $config->packages[$packageUrl] = $package;
    json_put($project->configFilePath->toString(), $config->toArray());

    success("Package $packageUrl has been added successfully.");
}

function add(Project $project, Config $config, Package $package, $packageUrl)
{
    $package->detectHash()->download($package->root($project, $config)->toString());

    $meta = Meta::fromArray(json_to_array($project->configLockFilePath->toString()));
    $meta->packages[$packageUrl] = $package;
    json_put($project->configLockFilePath->toString(), $meta->toArray());

    $packageConfig = Config::fromArray(json_to_array($package->configPath($project, $config)->toString()));

    foreach ($packageConfig->packages as $subPackageUrl => $subPackage) {
        $subPackageExists = false;
        foreach ($meta->packages as $installedPackageUrl => $installedPackage) {
            if ($installedPackage->is($subPackage)) {
                $subPackageExists = true;
                break;
            }
        }

        if (! $subPackageExists) {
            add($project, $config, $subPackage, $subPackageUrl);
        }
    }
}
