<?php

namespace Saeghe\Saeghe\Commands\Credential;

use Saeghe\FileManager\FileType\Json;
use Saeghe\Saeghe\Classes\Credential\Credential;
use Saeghe\Saeghe\Classes\Credential\Credentials;
use Saeghe\Saeghe\Classes\Environment\Environment;
use function Saeghe\Cli\IO\Read\argument;
use function Saeghe\Cli\IO\Write\success;

function run(Environment $environment): void
{
    $provider = argument(2);
    $token = argument(3);

    $credentials = $environment->credential_file->path->exists()
        ? Credentials::from_array(Json\to_array($environment->credential_file->path))
        : new Credentials();

    $credentials->push(new Credential($provider, $token));

    Json\write($environment->credential_file->path, $credentials->to_array());

    success("Credential for $provider has been set successfully.");
}
