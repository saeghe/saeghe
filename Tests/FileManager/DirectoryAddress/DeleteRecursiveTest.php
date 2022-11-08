<?php

namespace Tests\FileManager\DirectoryAddress\DeleteRecursiveTest;

use Saeghe\Saeghe\FileManager\DirectoryAddress;
use function Saeghe\Saeghe\FileManager\Directory\exists;

test(
    title: 'it should delete a directory recursively',
    case: function (DirectoryAddress $directory, DirectoryAddress $subdirectory) {
        $response = $directory->delete_recursive();
        assert_true($directory->to_string() === $response->to_string());
        assert_false(exists($directory->to_string()));
        assert_false(exists($subdirectory->to_string()));

        return $directory;
    },
    before: function () {
        $directory = DirectoryAddress::from_string(root() . 'Tests/PlayGround/DirectoryAddress');
        $subdirectory = DirectoryAddress::from_string(root() . 'Tests/PlayGround/DirectoryAddress/Subdirectory');
        $subdirectory->make_recursive();

        return [$directory, $subdirectory];
    }
);
