<?php

namespace Tests\FileManager\DirectoryAddress\DeleteTest;

use Saeghe\Saeghe\FileManager\DirectoryAddress;
use function Saeghe\Saeghe\FileManager\Directory\exists;


test(
    title: 'it should delete a directory',
    case: function (DirectoryAddress $directory) {
        $response = $directory->delete();
        assert_true($directory->to_string() === $response->to_string());
        assert_false(exists($directory->to_string()));
    },
    before: function () {
        $directory = DirectoryAddress::from_string(root() . 'Tests/PlayGround/DirectoryAddress');
        $directory->make();

        return $directory;
    }
);
