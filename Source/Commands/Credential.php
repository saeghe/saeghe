<?php

namespace Saeghe\Saeghe\Commands\Credential;

use Saeghe\Saeghe\Project;
use function Saeghe\Cli\IO\Read\argument;
use function Saeghe\Cli\IO\Write\success;

function run(Project $project)
{
    $provider = argument(2);
    $token = argument(3);

    $credentials = json_to_array($project->credentials_path->to_string());
    $credentials[$provider] = ['token' => $token];
    json_put($project->credentials_path->to_string(), $credentials);

    success("Credential for $provider has been set successfully.");
}
