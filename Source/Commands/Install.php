<?php

namespace Saeghe\Saeghe\Commands\Install;

use Saeghe\FileManager\FileType\Json;
use Saeghe\Saeghe\Classes\Config\Config;
use Saeghe\Saeghe\Classes\Environment\Environment;
use Saeghe\Saeghe\Classes\Meta\Meta;
use Saeghe\Saeghe\Classes\Meta\Dependency;
use Saeghe\Saeghe\Classes\Package\Package;
use Saeghe\Saeghe\Classes\Project\Project;
use function Saeghe\Cli\IO\Read\parameter;
use function Saeghe\Cli\IO\Write\error;
use function Saeghe\Cli\IO\Write\line;
use function Saeghe\Cli\IO\Write\success;

function run(Environment $environment): void
{
    line('Installing packages...');

    $project = new Project($environment->pwd->subdirectory(parameter('project', '')));

    if (! $project->config_file->exists()) {
        error('Project is not initialized. Please try to initialize using the init command.');
        return;
    }

    line('Setting env credential...');
    set_credentials($environment);

    line('Loading configs...');
    $project->config(Config::from_array(Json\to_array($project->config_file)));
    $project->meta = Meta::from_array(Json\to_array($project->meta_file));

    $project->packages_directory->exists_or_create();

    line('Downloading packages...');
    $project->meta->dependencies->each(function (Dependency $dependency) use ($project) {
        $package = new Package($project->package_directory($dependency->repository()), $dependency->repository());
        line('Downloading package ' . $dependency->key . ' to ' . $package->root);
        $package->download();
    });

    success('Packages has been installed successfully.');
}
