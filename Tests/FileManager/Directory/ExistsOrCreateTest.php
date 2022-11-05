<?php

namespace Tests\FileManager\Directory\ExistsOrCreate;

use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\FileManager\Directory;

test(
    title: 'it should return true when directory exists',
    case: function (Address $directory) {
        assert_true(Directory\exists_or_create($directory->to_string()));

        return $directory;
    },
    before: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/ExistsOrCreate');
        Directory\make($directory->to_string());

        return $directory;
    },
    after: function (Address $directory) {
        Directory\delete($directory->to_string());
    }
);

test(
    title: 'it should create and return true when directory not exists',
    case: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/ExistsOrCreate');

        assert_true(Directory\exists_or_create($directory->to_string()));
        assert_true(Directory\exists($directory->to_string()));

        return $directory;
    },
    after: function (Address $directory) {
        Directory\delete($directory->to_string());
    }
);
