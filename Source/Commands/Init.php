<?php

namespace Saeghe\Saeghe\Commands\Init;

use Saeghe\FileManager\Filesystem\Filename;
use Saeghe\FileManager\FileType\Json;
use Saeghe\Saeghe\Classes\Config\Config;
use Saeghe\Saeghe\Classes\Meta\Meta;
use Saeghe\Saeghe\Classes\Environment\Environment;
use Saeghe\Saeghe\Classes\Project\Project;
use function Saeghe\Cli\IO\Read\parameter;
use function Saeghe\Cli\IO\Write\error;
use function Saeghe\Cli\IO\Write\line;
use function Saeghe\Cli\IO\Write\success;

function run(Environment $environment): void
{
    line('Init project...');
    $project = new Project($environment->pwd->subdirectory(parameter('project', '')));

    if ($project->config_file->exists()) {
        error('The project is already initialized.');
        return;
    }

    $config = pipe(Config::init(), function (Config $config) {
        $config->packages_directory = new Filename(parameter('packages-directory', $config->packages_directory->string()));

        return $config;
    });

    $meta = Meta::init();

    $project->config($config);
    $project->meta = $meta;

    Json\write($project->config_file, $project->config->to_array());
    Json\write($project->meta_file, $project->meta->to_array());

    $project->packages_directory->exists_or_create();

    success('Project has been initialized.');
}
