<?php

namespace Tests\Datatype\ArrayCollection\ArrayCollectionTest;

use Saeghe\Saeghe\Datatype\ArrayCollection;
use Saeghe\Saeghe\Datatype\Collection;

test(
    title: 'it should create a collection instance that accept any value',
    case: function () {
        $collection = new ArrayCollection(['foo', 2 => 'bar', '3' => 'baz', null => 'qux']);

        assert_true($collection instanceof Collection);
        assert_true(['foo', 2 => 'bar', '3' => 'baz', null => 'qux'] === $collection->items());
    }
);
