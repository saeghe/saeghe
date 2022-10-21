<?php

namespace Saeghe\Saeghe\Commands\Credential;

use function Saeghe\Cli\IO\Read\argument;
use function Saeghe\Cli\IO\Write\success;

function run()
{
    global $credentialsPath;

    $provider = argument(2);
    $token = argument(3);

    $credentials = json_to_array($credentialsPath, []);

    $credentials[$provider] = ['token' => $token];

    json_put($credentialsPath, $credentials);

    success("Credential for $provider has been set successfully.");
}
