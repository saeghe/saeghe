<?php

namespace Tests\FileManager\Filesystem\Directory\DeleteTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use function Saeghe\Saeghe\FileManager\Directory\exists;


test(
    title: 'it should delete a directory',
    case: function (Directory $directory) {
        $response = $directory->delete();
        assert_true($directory->path->string() === $response->path->string());
        assert_false(exists($directory));
    },
    before: function () {
        $directory = Directory::from_string(root() . 'Tests/PlayGround/DirectoryAddress');
        $directory->make();

        return $directory;
    }
);
