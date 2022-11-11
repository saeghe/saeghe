<?php

namespace Saeghe\Saeghe\Commands\Init;

use Saeghe\Saeghe\Config\Config;
use Saeghe\Saeghe\Meta;
use Saeghe\Saeghe\Project;
use function Saeghe\Cli\IO\Read\parameter;
use function Saeghe\Cli\IO\Write\success;
use function Saeghe\Saeghe\FileManager\FileType\Json\write;

function run(Project $project)
{
    $config = Config::init()->to_array();
    $config['packages-directory'] = parameter('packages-directory', 'Packages');

    write($project->config->stringify(), $config);
    write($project->config_lock->stringify(), Meta::init()->to_array());

    $project->root->subdirectory($config['packages-directory'])->exists_or_create();

    success('Project has been initialized.');
}
