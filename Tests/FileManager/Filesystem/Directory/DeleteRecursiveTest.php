<?php

namespace Tests\FileManager\Filesystem\Directory\DeleteRecursiveTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use function Saeghe\Saeghe\FileManager\Directory\exists;

test(
    title: 'it should delete a directory recursively',
    case: function (Directory $directory, Directory $subdirectory) {
        $response = $directory->delete_recursive();
        assert_true($directory->stringify() === $response->stringify());
        assert_false(exists($directory->stringify()));
        assert_false(exists($subdirectory->stringify()));

        return $directory;
    },
    before: function () {
        $directory = new Directory(root() . 'Tests/PlayGround/DirectoryAddress');
        $subdirectory = new Directory(root() . 'Tests/PlayGround/DirectoryAddress/Subdirectory');
        $subdirectory->make_recursive();

        return [$directory, $subdirectory];
    }
);
