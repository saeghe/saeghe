<?php

namespace Tests\FileManager\Directory\PermissionTest;

use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\FileManager\Directory;

test(
    title: 'it should return directory\'s permission',
    case: function () {
        $playGround = Address::from_string(root() . 'Tests/PlayGround');
        $regular = $playGround->append('regular');
        Directory\make($regular->to_string(), 0774);
        assert_true(0774 === Directory\permission($regular->to_string()));

        $full = $playGround->append('full');
        Directory\make($full->to_string(), 0777);
        assert_true(0777 === Directory\permission($full->to_string()));

        return [$regular, $full];
    },
    after: function (Address $regular, Address $full) {
        Directory\delete($regular->to_string());
        Directory\delete($full->to_string());
    }
);

test(
    title: 'it should not return cached permission',
    case: function () {
        $playGround = Address::from_string(root() . 'Tests/PlayGround');
        $directory = $playGround->append('regular');
        Directory\make($directory->to_string(), 0775);
        assert_true(0775 === Directory\permission($directory->to_string()));
        chmod($directory->to_string(), 0774);
        assert_true(0774 === Directory\permission($directory->to_string()));

        return $directory;
    },
    after: function (Address $directory) {
        Directory\delete($directory->to_string());
    }
);
