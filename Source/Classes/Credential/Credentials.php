<?php

namespace Saeghe\Saeghe\Classes\Credential;

use Saeghe\Datatype\Collection;
use function Saeghe\Datatype\Arr\reduce;

class Credentials extends Collection
{
    public static function from_array(array $credentials): static
    {
        return reduce($credentials, function (Credentials $carry, array $provider_credentials, string $provider) {
            return $carry->push(new Credential($provider, $provider_credentials['token']));
        }, new static());
    }

    public function to_array(): array
    {
        return $this->reduce(function (array $carry, Credential $credential) {
            $carry[$credential->provider()] = ['token' => $credential->token()];

            return $carry;
        }, []);
    }
}
