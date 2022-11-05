<?php

namespace Tests\FileManager\Directory\makeTest;

use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\FileManager\Directory;

test(
    title: 'it should make a directory',
    case: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/MakeDirectory');

        assert_true(Directory\make($directory->to_string()));
        assert_true(Directory\exists($directory->to_string()));
        assert_true(0775 === Directory\permission($directory->to_string()));

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

        assert_true(Directory\make($directory->to_string(), 0777));
        assert_true(Directory\exists($directory->to_string()));
        assert_true(0777 === Directory\permission($directory->to_string()));

        return $directory;
    },
    after: function (Address $address) {
        Directory\delete($address->to_string());
    }
);
