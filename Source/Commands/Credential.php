<?php

namespace Saeghe\Saeghe\Commands\Credential;

use Saeghe\Saeghe\Project;
use Saeghe\Saeghe\FileManager\File;
use Saeghe\Saeghe\FileManager\FileType\Json;
use function Saeghe\Cli\IO\Read\argument;
use function Saeghe\Cli\IO\Write\success;

function run(Project $project)
{
    $provider = argument(2);
    $token = argument(3);

    $credentials = File\exists($project->credentials_path->to_string())
        ? Json\to_array($project->credentials_path->to_string())
        : [];
    $credentials[$provider] = ['token' => $token];
    Json\write($project->credentials_path->to_string(), $credentials);

    success("Credential for $provider has been set successfully.");
}
