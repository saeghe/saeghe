<?php

namespace Tests\FileManager\Filesystem\Directory\PermissionTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should return directory\'s permission',
    case: function () {
        $playGround = Directory::from_string(root() . 'Tests/PlayGround');
        $regular = $playGround->subdirectory('regular');
        $regular->make(0774);
        assert_true(0774 === $regular->permission());

        $full = $playGround->subdirectory('full');
        $full->make(0777);
        assert_true(0777 === $full->permission());

        return [$regular, $full];
    },
    after: function (Directory $regular, Directory $full) {
        $regular->delete();
        $full->delete();
    }
);

test(
    title: 'it should not return cached permission',
    case: function () {
        $playGround = Directory::from_string(root() . 'Tests/PlayGround');
        $directory = $playGround->subdirectory('regular');
        $directory->make(0775);
        assert_true(0775 === $directory->permission());
        chmod($directory, 0774);
        assert_true(0774 === $directory->permission());

        return $directory;
    },
    after: function (Directory $directory) {
        $directory->delete();
    }
);
