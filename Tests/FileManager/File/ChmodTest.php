<?php

namespace Tests\FileManager\File\ChmodTest;

use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\FileManager\File;

test(
    title: 'it should change file\'s permission',
    case: function () {
        $playGround = Address::from_string(root() . 'Tests/PlayGround');
        $regular = $playGround->append('regular');
        File\create($regular->to_string(), 'content');
        assert_true(File\chmod($regular->to_string(), 0664));
        assert_true(0664 === File\permission($regular->to_string()));

        $full = $playGround->append('full');
        File\create($full->to_string(), 'full');
        assert_true(File\chmod($full->to_string(), 0777));
        assert_true(0777 === File\permission($full->to_string()));

        return [$regular, $full];
    },
    after: function (Address $regular, Address $full) {
        File\delete($regular->to_string());
        File\delete($full->to_string());
    }
);
