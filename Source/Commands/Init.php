<?php

namespace Saeghe\Saeghe\Commands\Init;

use Saeghe\Saeghe\Config;
use Saeghe\Saeghe\Meta;
use Saeghe\Saeghe\Project;
use function Saeghe\Cli\IO\Read\parameter;
use function Saeghe\Cli\IO\Write\success;

function run(Project $project)
{
    $config = Config::init()->toArray();
    $config['packages-directory'] = parameter('packages-directory', 'Packages');

    json_put($project->configFilePath->toString(), $config);
    json_put($project->configLockFilePath->toString(), Meta::init()->toArray());

    dir_find_or_create($project->root->append($config['packages-directory'])->toString());

    success('Project has been initialized.');
}
