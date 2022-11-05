<?php

namespace Saeghe\Saeghe\Commands\Init;

use Saeghe\Saeghe\Config;
use Saeghe\Saeghe\Meta;
use Saeghe\Saeghe\Project;
use function Saeghe\Cli\IO\Read\parameter;
use function Saeghe\Cli\IO\Write\success;
use function Saeghe\Saeghe\FileManager\Directory\exists_or_create;
use function Saeghe\Saeghe\FileManager\FileType\Json\write;

function run(Project $project)
{
    $config = Config::init()->to_array();
    $config['packages-directory'] = parameter('packages-directory', 'Packages');

    write($project->config_file_path->to_string(), $config);
    write($project->config_lock_file_path->to_string(), Meta::init()->to_array());

    exists_or_create($project->root->append($config['packages-directory'])->to_string());

    success('Project has been initialized.');
}
