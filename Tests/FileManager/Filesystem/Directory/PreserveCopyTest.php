<?php

namespace Tests\FileManager\Filesystem\Directory\PreserveCopyTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;

test(
    title: 'it should copy directory by preserving permission',
    case: function (Directory $origin, Directory $destination) {
        $copied_directory = $destination->subdirectory($origin->leaf());
        $result = $origin->preserve_copy($copied_directory);

        assert_true($result->stringify() === $origin->stringify());
        assert_true($copied_directory->exists());
        assert_true($origin->permission() === $copied_directory->permission());

        return [$origin, $destination];
    },
    before: function () {
        $origin = new Directory(root() . 'Tests/PlayGround/Origin/PreserveCopy');
        $origin->make_recursive();
        $destination = new Directory(root() . 'Tests/PlayGround/Destination');
        $destination->make();

        return [$origin, $destination];
    },
    after: function (Directory $origin, Directory $destination) {
        $origin->parent()->delete_recursive();
        $destination->delete_recursive();
    }
);

test(
    title: 'it should copy directory by preserving permission with any permission',
    case: function (Directory $origin, Directory $destination) {
        $copied_directory = $destination->subdirectory($origin->leaf());
        $origin->preserve_copy($copied_directory);

        assert_true($copied_directory->exists());
        assert_true(0777 === $copied_directory->permission());

        return [$origin, $destination];
    },
    before: function () {
        $origin = new Directory(root() . 'Tests/PlayGround/Origin/PreserveCopy');
        $origin->make_recursive(0777);
        $destination = new Directory(root() . 'Tests/PlayGround/Destination');
        $destination->make();

        return [$origin, $destination];
    },
    after: function (Directory $origin, Directory $destination) {
        $origin->parent()->delete_recursive();
        $destination->delete_recursive();
    }
);
