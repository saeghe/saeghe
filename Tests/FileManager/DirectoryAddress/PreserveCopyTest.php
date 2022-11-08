<?php

namespace Tests\FileManager\DirectoryAddress\PreserveCopyTest;

use Saeghe\Saeghe\FileManager\DirectoryAddress;

test(
    title: 'it should copy directory by preserving permission',
    case: function (DirectoryAddress $origin, DirectoryAddress $destination) {
        $copied_directory = $destination->subdirectory($origin->leaf());
        $result = $origin->preserve_copy($copied_directory);

        assert_true($result->to_string() === $origin->to_string());
        assert_true($copied_directory->exists());
        assert_true($origin->permission() === $copied_directory->permission());

        return [$origin, $destination];
    },
    before: function () {
        $origin = DirectoryAddress::from_string(root() . 'Tests/PlayGround/Origin/PreserveCopy');
        $origin->make_recursive();
        $destination = DirectoryAddress::from_string(root() . 'Tests/PlayGround/Destination');
        $destination->make();

        return [$origin, $destination];
    },
    after: function (DirectoryAddress $origin, DirectoryAddress $destination) {
        $origin->parent()->delete_recursive();
        $destination->delete_recursive();
    }
);

test(
    title: 'it should copy directory by preserving permission with any permission',
    case: function (DirectoryAddress $origin, DirectoryAddress $destination) {
        $copied_directory = $destination->subdirectory($origin->leaf());
        $origin->preserve_copy($copied_directory);

        assert_true($copied_directory->exists());
        assert_true(0777 === $copied_directory->permission());

        return [$origin, $destination];
    },
    before: function () {
        $origin = DirectoryAddress::from_string(root() . 'Tests/PlayGround/Origin/PreserveCopy');
        $origin->make_recursive(0777);
        $destination = DirectoryAddress::from_string(root() . 'Tests/PlayGround/Destination');
        $destination->make();

        return [$origin, $destination];
    },
    after: function (DirectoryAddress $origin, DirectoryAddress $destination) {
        $origin->parent()->delete_recursive();
        $destination->delete_recursive();
    }
);
