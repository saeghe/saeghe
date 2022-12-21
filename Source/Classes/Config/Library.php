<?php

namespace Saeghe\Saeghe\Classes\Config;

use Saeghe\Datatype\Pair;
use Saeghe\Saeghe\Git\Repository;

class Library extends Pair
{
    public function repository(): Repository
    {
        return $this->value;
    }

    public function meta(): array
    {
        return [
            'owner' => $this->value->owner,
            'repo' => $this->value->repo,
            'version' => $this->value->version,
            'hash' => $this->value->hash,
        ];
    }
}
