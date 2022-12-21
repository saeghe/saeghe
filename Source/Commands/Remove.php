<?php

namespace Saeghe\Saeghe\Commands\Remove;

use Saeghe\FileManager\FileType\Json;
use Saeghe\Saeghe\Classes\Config\Config;
use Saeghe\Saeghe\Classes\Config\Library;
use Saeghe\Saeghe\Classes\Environment\Environment;
use Saeghe\Saeghe\Classes\Meta\Meta;
use Saeghe\Saeghe\Classes\Meta\Dependency;
use Saeghe\Saeghe\Classes\Package\Package;
use Saeghe\Saeghe\Classes\Project\Project;
use Saeghe\Saeghe\Git\Repository;
use function Saeghe\Cli\IO\Read\argument;
use function Saeghe\Cli\IO\Read\parameter;
use function Saeghe\Cli\IO\Write\error;
use function Saeghe\Cli\IO\Write\line;
use function Saeghe\Cli\IO\Write\success;

function run(Environment $environment): void
{
    $package_url = argument(2);
    $repository = Repository::from_url($package_url);
    line('Removing package ' . $package_url);

    $project = new Project($environment->pwd->subdirectory(parameter('project', '')));

    if (! $project->config_file->exists()) {
        error('Project is not initialized. Please try to initialize using the init command.');
        return;
    }

    line('Loading configs...');
    $project->config(Config::from_array(Json\to_array($project->config_file)));
    $project->meta = Meta::from_array(Json\to_array($project->meta_file));

    line('Finding package in configs...');
    $library = $project->config->repositories->first(fn (Library $library) => $library->repository()->is($repository));
    $dependency = $project->meta->dependencies->first(fn (Dependency $dependency) => $dependency->repository()->is($library->repository()));
    if (! $library instanceof Library || ! $dependency instanceof Dependency) {
        error("Package $package_url does not found in your project!");
        return;
    }

    line('Loading package\'s config...');
    $project->meta->dependencies->each(function (Dependency $dependency) use ($project) {
        $package = new Package($project->package_directory($dependency->repository()), $dependency->repository());
        $package->config = Config::from_array(Json\to_array($package->config_file));
        $project->packages->push($package);
    });

    line('Removing package from config...');
    unless(
        $project->packages->has(fn (Package $package)
            => $package->config->repositories->has(fn (Library $library)
                => $library->repository()->is($dependency->repository()))),
        fn () => remove($project, $dependency)
    );

    $project->config->repositories->forget(fn (Library $installed_library)
        => $installed_library->repository()->is($library->repository()));

    line('Committing configs...');
    Json\write($project->config_file, $project->config->to_array());
    Json\write($project->meta_file, $project->meta->to_array());

    success("Package $package_url has been removed successfully.");
}

function remove(Project $project, Dependency $dependency): void
{
    $package = $project->packages->take(fn (Package $package) => $package->repository->is($dependency->repository()));

    $package->config->repositories->each(function (Library $sub_library) use ($project) {
        $dependency = $project->meta->dependencies->first(fn (Dependency $dependency) => $dependency->repository()->is($sub_library->repository()));
        unless(
            $project->config->repositories->has(fn (Library $library) => $library->repository()->is($dependency->repository())),
            fn () => remove($project, $dependency)
        );
    });

    unless(
        $project->packages->has(fn (Package $package)
            => $package->repository->is($dependency->repository())),
        fn () => $package->root->delete_recursive()
            && $project->meta->dependencies->forget(fn (Dependency $meta_dependency)
            => $meta_dependency->repository()->is($dependency->repository()))
    );
}
