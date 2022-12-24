<?php

namespace Saeghe\Saeghe\Commands\Run;

use Saeghe\FileManager\Filesystem\Filename;
use Saeghe\FileManager\FileType\Json;
use Saeghe\FileManager\Filesystem\Directory;
use Saeghe\Saeghe\Classes\Build\Build;
use Saeghe\Saeghe\Classes\Config\Config;
use Saeghe\Saeghe\Classes\Config\LinkPair;
use Saeghe\Saeghe\Classes\Environment\Environment;
use Saeghe\Saeghe\Classes\Meta\Dependency;
use Saeghe\Saeghe\Classes\Meta\Meta;
use Saeghe\Saeghe\Classes\Package\Package;
use Saeghe\Saeghe\Classes\Project\Project;
use Saeghe\Saeghe\Git\Repository;
use function Saeghe\Cli\IO\Read\argument;
use function Saeghe\Cli\IO\Write\error;
use function Saeghe\Saeghe\Commands\Build\add_autoloads;
use function Saeghe\Saeghe\Commands\Build\add_executables;
use function Saeghe\Saeghe\Commands\Build\compile_packages;
use function Saeghe\Saeghe\Commands\Build\compile_project_files;

function run(Environment $environment): void
{
    $package_url = argument(2);

    set_credentials($environment);

    $repository = Repository::from_url($package_url)->latest_version()->detect_hash();

    $root = Directory::from_string(sys_get_temp_dir())->subdirectory('saeghe/runner/' . $repository->owner . '/' . $repository->repo);

    if (! $repository->file_exists('saeghe.config.json')) {
        error("Given $package_url URL is not a Saeghe package.");
        return;
    }

    $repository->download($root);

    $project = new Project($root);

    $project->config(Config::from_array(Json\to_array($project->config_file)));
    $project->meta = Meta::from_array(Json\to_array($project->meta_file));

    $project->packages_directory->exists_or_create();

    $project->meta->dependencies->each(function (Dependency $dependency) use ($project) {
        $package = new Package($project->package_directory($dependency->repository()), $dependency->repository());
        $package->download();
    });

    $build = new Build($project, 'production');
    $build->root()->renew_recursive();
    $build->packages_directory()->exists_or_create();
    $build->load_namespace_map();

    $project->packages->each(function (Package $package) use ($project, $build) {
        compile_packages($package, $build);
    });

    compile_project_files($build);

    $project->config->entry_points->each(function (Filename $entry_point) use ($build) {
        add_autoloads($build, $build->root()->file($entry_point));
    });

    $project->packages->each(function (Package $package)  use ($project, $build) {
        $package->config->executables->each(function (LinkPair $executable) use ($build, $package) {
            add_executables($build, $build->package_root($package)->file($executable->source()), $build->root()->symlink($executable->symlink()));
        });
    });

    $entry_point = argument(3) ? argument(3) : $project->config->entry_points->first();

    $entry_point_path = $build->root()->file($entry_point)->path;

    if (! $entry_point_path->exists()) {
        error("Entry point $entry_point is not defined in the package.");
        return;
    }

    include_once $entry_point_path->string();
}
