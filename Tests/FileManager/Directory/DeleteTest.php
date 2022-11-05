<?php

namespace Tests\FileManager\Directory\DeleteTest;

use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\FileManager\Directory;

test(
    title: 'it should delete the given directory',
    case: function (Address $directory) {
        Directory\delete($directory->to_string());

        assert_false(Directory\exists($directory->to_string()));
    },
    before: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/DeleteDirectory');
        mkdir($directory->to_string());

        return $directory;
    }
);
