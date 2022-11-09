<?php

namespace Tests\FileManager\Directory\ChmodTest;

use Saeghe\Saeghe\FileManager\Path;
use Saeghe\Saeghe\FileManager\Directory;

test(
    title: 'it should change directory\'s permission',
    case: function () {
        $playGround = Path::from_string(root() . 'Tests/PlayGround');
        $regular = $playGround->append('regular');
        Directory\make($regular->stringify(), 0666);
        assert_true(Directory\chmod($regular->stringify(), 0774));
        assert_true(0774 === Directory\permission($regular->stringify()));

        $full = $playGround->append('full');
        Directory\make($full->stringify(), 0755);
        assert_true(Directory\chmod($full->stringify(), 0777));
        assert_true(0777 === Directory\permission($full->stringify()));

        return [$regular, $full];
    },
    after: function (Path $regular, Path $full) {
        Directory\delete($regular->stringify());
        Directory\delete($full->stringify());
    }
);
