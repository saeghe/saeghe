<?php

namespace Tests\FileManager\Directory\ChmodTest;

use Saeghe\Saeghe\FileManager\Path;
use Saeghe\Saeghe\FileManager\Directory;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should change directory\'s permission',
    case: function () {
        $playGround = Path::from_string(root() . 'Tests/PlayGround');
        $regular = $playGround->append('regular');
        Directory\make($regular, 0666);
        assert_true(Directory\chmod($regular, 0774));
        assert_true(0774 === Directory\permission($regular));

        $full = $playGround->append('full');
        Directory\make($full, 0755);
        assert_true(Directory\chmod($full, 0777));
        assert_true(0777 === Directory\permission($full));

        return [$regular, $full];
    },
    after: function (Path $regular, Path $full) {
        Directory\delete($regular);
        Directory\delete($full);
    }
);
