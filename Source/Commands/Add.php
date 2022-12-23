<?php

namespace Saeghe\Saeghe\Commands\Add;

use Saeghe\FileManager\FileType\Json;
use Saeghe\Saeghe\Classes\Config\PackageAlias;
use Saeghe\Saeghe\Classes\Config\Config;
use Saeghe\Saeghe\Classes\Config\Library;
use Saeghe\Saeghe\Classes\Environment\Environment;
use Saeghe\Saeghe\Classes\Meta\Meta;
use Saeghe\Saeghe\Classes\Meta\Dependency;
use Saeghe\Saeghe\Classes\Package\Package;
use Saeghe\Saeghe\Classes\Project\Project;
use Saeghe\Saeghe\Git\Repository;
use function Saeghe\Cli\IO\Read\parameter;
use function Saeghe\Cli\IO\Read\argument;
use function Saeghe\Cli\IO\Write\error;
use function Saeghe\Cli\IO\Write\line;
use function Saeghe\Cli\IO\Write\success;

function run(Environment $environment): void
{
    $package_url = argument(2);
    $version = parameter('version');

    line('Adding package ' . $package_url . ($version ? ' version ' . $version : ' latest version') . '...');

    $project = new Project($environment->pwd->subdirectory(parameter('project', '')));

    if (! $project->config_file->exists()) {
        error('Project is not initialized. Please try to initialize using the init command.');
        return;
    }

    line('Setting env credential...');
    set_credentials($environment);

    line('Loading configs...');
    $project->config(Config::from_array(Json\to_array($project->config_file)));
    $project->meta = $project->meta_file->exists() ? Meta::from_array(Json\to_array($project->meta_file)) : Meta::init();

    $package_url = when_exists(
        $project->config->aliases->first(fn (PackageAlias $package_alias) => $package_alias->alias() === $package_url),
        fn (PackageAlias $package_alias) => $package_alias->package_url(),
        fn () => $package_url
    );
    $repository = Repository::from_url($package_url);

    line('Checking installed packages...');
    if ($project->config->repositories->has(fn (Library $library) => $library->repository()->is($repository))) {
        error("Package $package_url is already exists.");
        return;
    }

    line('Setting package version...');
    $version ? $repository->version($version) : $repository->latest_version();
    $library = new Library($package_url, $repository);

    line('Creating package directory...');
    unless($project->packages_directory->exists(), fn () => $project->packages_directory->make_recursive());

    line('Detecting version hash...');
    $library->repository()->detect_hash();

    line('Validating the package...');
    if (! $library->repository()->file_exists('saeghe.config.json')) {
        error("Given $package_url URL is not a Saeghe package.");
        return;
    }

    line('Downloading the package...');
    $dependency = new Dependency($package_url, $library->meta());
    add($project, $dependency);

    line('Updating configs...');
    $project->config->repositories->push($library);

    line('Committing configs...');
    Json\write($project->config_file, $project->config->to_array());
    Json\write($project->meta_file, $project->meta->to_array());

    success("Package $package_url has been added successfully.");
}

function add(Project $project, Dependency $dependency): void
{
    $package = new Package($project->package_directory($dependency->repository()), $dependency->repository());

    unless($package->is_downloaded(), fn () => $package->download() && $project->meta->dependencies->push($dependency));

    $package->config = Config::from_array(Json\to_array($package->config_file));

    $package->config->repositories
        ->except(fn (Library $library)
            => $project->meta->dependencies->has(fn (Dependency $dependency)
                => $dependency->repository()->is($library->repository())))
        ->each(function (Library $library) use ($project) {
            $library->repository()->detect_hash();
            add($project, new Dependency($library->key, $library->meta()));
        });
}
