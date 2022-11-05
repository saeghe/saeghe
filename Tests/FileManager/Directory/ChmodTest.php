<?php

namespace Tests\FileManager\Directory\ChmodTest;

use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\FileManager\Directory;

test(
    title: 'it should change directory\'s permission',
    case: function () {
        $playGround = Address::from_string(root() . 'Tests/PlayGround');
        $regular = $playGround->append('regular');
        Directory\make($regular->to_string(), 0666);
        assert_true(Directory\chmod($regular->to_string(), 0774));
        assert_true(0774 === Directory\permission($regular->to_string()));

        $full = $playGround->append('full');
        Directory\make($full->to_string(), 0755);
        assert_true(Directory\chmod($full->to_string(), 0777));
        assert_true(0777 === Directory\permission($full->to_string()));

        return [$regular, $full];
    },
    after: function (Address $regular, Address $full) {
        Directory\delete($regular->to_string());
        Directory\delete($full->to_string());
    }
);
