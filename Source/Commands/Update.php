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

function run(Project $project)
{
    $givenPackageUrl = argument(2);
    $version = parameter('version');

    $config = Config::fromArray(json_to_array($project->configFilePath->toString()));

    $package = array_reduce(
        $config->packages,
        function ($carry, Package $package) {
            return $package->is($carry) ? $package : $carry;
        },
        Package::fromUrl($givenPackageUrl)
    );

    if (! isset($package->version)) {
        error("Package $givenPackageUrl does not found in your project!");
        return;
    }

    $version ? $package->version($version) : $package->latestVersion();

    $packageUrl = $givenPackageUrl;

    foreach ($config->packages as $installedPackageUrl => $configPackage) {
        if ($configPackage->is($package)) {
            $packageUrl = $installedPackageUrl;
            break;
        }
    }

    remove($project, $config, $package, $packageUrl);
    add($project, $config, $package, $packageUrl);

    $config->packages[$packageUrl] = $package;
    json_put($project->configFilePath->toString(), $config->toArray());

    success("Package $givenPackageUrl has been updated.");
}
