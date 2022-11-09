<?php

namespace Tests\FileManager\Directory\PermissionTest;

use Saeghe\Saeghe\FileManager\Path;
use Saeghe\Saeghe\FileManager\Directory;

test(
    title: 'it should return directory\'s permission',
    case: function () {
        $playGround = Path::from_string(root() . 'Tests/PlayGround');
        $regular = $playGround->append('regular');
        Directory\make($regular->stringify(), 0774);
        assert_true(0774 === Directory\permission($regular->stringify()));

        $full = $playGround->append('full');
        Directory\make($full->stringify(), 0777);
        assert_true(0777 === Directory\permission($full->stringify()));

        return [$regular, $full];
    },
    after: function (Path $regular, Path $full) {
        Directory\delete($regular->stringify());
        Directory\delete($full->stringify());
    }
);

test(
    title: 'it should not return cached permission',
    case: function () {
        $playGround = Path::from_string(root() . 'Tests/PlayGround');
        $directory = $playGround->append('regular');
        Directory\make($directory->stringify(), 0775);
        assert_true(0775 === Directory\permission($directory->stringify()));
        chmod($directory->stringify(), 0774);
        assert_true(0774 === Directory\permission($directory->stringify()));

        return $directory;
    },
    after: function (Path $directory) {
        Directory\delete($directory->stringify());
    }
);
