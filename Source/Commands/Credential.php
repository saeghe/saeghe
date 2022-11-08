<?php

namespace Saeghe\Saeghe\Commands\Credential;

use Saeghe\Saeghe\Project;
use Saeghe\Saeghe\FileManager\FileType\Json;
use function Saeghe\Cli\IO\Read\argument;
use function Saeghe\Cli\IO\Write\success;

function run(Project $project)
{
    $provider = argument(2);
    $token = argument(3);

    $credentials = $project->credentials->exists()
        ? Json\to_array($project->credentials->to_string())
        : [];
    $credentials[$provider] = ['token' => $token];
    Json\write($project->credentials->to_string(), $credentials);

    success("Credential for $provider has been set successfully.");
}
