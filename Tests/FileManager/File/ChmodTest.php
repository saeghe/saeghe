<?php

namespace Tests\FileManager\File\ChmodTest;

use Saeghe\Saeghe\FileManager\Path;
use Saeghe\Saeghe\FileManager\File;

test(
    title: 'it should change file\'s permission',
    case: function () {
        $playGround = Path::from_string(root() . 'Tests/PlayGround');
        $regular = $playGround->append('regular');
        File\create($regular, 'content');
        assert_true(File\chmod($regular, 0664));
        assert_true(0664 === File\permission($regular));

        $full = $playGround->append('full');
        File\create($full, 'full');
        assert_true(File\chmod($full, 0777));
        assert_true(0777 === File\permission($full));

        return [$regular, $full];
    },
    after: function (Path $regular, Path $full) {
        File\delete($regular);
        File\delete($full);
    }
);
