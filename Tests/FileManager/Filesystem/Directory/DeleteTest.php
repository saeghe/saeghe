<?php

namespace Tests\FileManager\Filesystem\Directory\DeleteTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use function Saeghe\Saeghe\FileManager\Directory\exists;


test(
    title: 'it should delete a directory',
    case: function (Directory $directory) {
        $response = $directory->delete();
        assert_true($directory->stringify() === $response->stringify());
        assert_false(exists($directory->stringify()));
    },
    before: function () {
        $directory = new Directory(root() . 'Tests/PlayGround/DirectoryAddress');
        $directory->make();

        return $directory;
    }
);
