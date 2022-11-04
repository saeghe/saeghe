<?php

namespace Tests\FileManager\Directory\makeTest;

use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\FileManager\Directory;

test(
    title: 'it should make a directory',
    case: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/MakeDirectory');

        assert(Directory\make($directory->to_string()));
        assert(Directory\exists($directory->to_string()));
        assert(0755 === Directory\permission($directory->to_string()));

        return $directory;
    },
    after: function (Address $address) {
        Directory\delete($address->to_string());
    }
);

test(
    title: 'it should make a directory with the given permission',
    case: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/MakeDirectory');

        assert(Directory\make($directory->to_string(), 0777));
        assert(Directory\exists($directory->to_string()));
        assert(0777 === Directory\permission($directory->to_string()));

        return $directory;
    },
    after: function (Address $address) {
        Directory\delete($address->to_string());
    }
);
