<?php

namespace Saeghe\Saeghe\Commands\Alias;

use Saeghe\FileManager\FileType\Json;
use Saeghe\Saeghe\Classes\Config\PackageAlias;
use Saeghe\Saeghe\Classes\Config\Config;
use Saeghe\Saeghe\Classes\Environment\Environment;
use Saeghe\Saeghe\Classes\Project\Project;
use function Saeghe\Cli\IO\Read\argument;
use function Saeghe\Cli\IO\Read\parameter;
use function Saeghe\Cli\IO\Write\error;
use function Saeghe\Cli\IO\Write\line;
use function Saeghe\Cli\IO\Write\success;

function run(Environment $environment): void
{
    $alias = argument(2);
    $package_url = argument(3);

    line("Registering alias $alias for $package_url...");

    $project = new Project($environment->pwd->subdirectory(parameter('project', '')));

    if (! $project->config_file->exists()) {
        error('Project is not initialized. Please try to initialize using the init command.');
        return;
    }

    $project->config(Config::from_array(Json\to_array($project->config_file)));

    $registered_alias = $project->config->aliases->first(fn (PackageAlias $package_alias) => $package_alias->alias() === $alias);

    if ($registered_alias) {
        error("The alias is already registered for $registered_alias->value.");
        return;
    }

    $project->config->aliases->push(new PackageAlias($alias, $package_url));

    Json\write($project->config_file, $project->config->to_array());

    success("Alias $alias has been registered for $package_url.");
}
