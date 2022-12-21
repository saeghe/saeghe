<?php

namespace Saeghe\Saeghe\Classes\Meta;

use Saeghe\Datatype\Pair;
use Saeghe\Saeghe\Git\Repository;

class Dependency extends Pair
{
    public function repository(): Repository
    {
        return Repository::from_meta($this->value);
    }
}
