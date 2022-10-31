<?php

namespace Saeghe\Saeghe\Commands\Init;

use Saeghe\Saeghe\Config;
use Saeghe\Saeghe\Meta;
use Saeghe\Saeghe\Project;
use function Saeghe\Cli\IO\Read\parameter;
use function Saeghe\Cli\IO\Write\success;

function run(Project $project)
{
    $config = Config::init()->to_array();
    $config['packages-directory'] = parameter('packages-directory', 'Packages');

    json_put($project->config_file_path->to_string(), $config);
    json_put($project->config_lock_file_path->to_string(), Meta::init()->to_array());

    dir_find_or_create($project->root->append($config['packages-directory'])->to_string());

    success('Project has been initialized.');
}
