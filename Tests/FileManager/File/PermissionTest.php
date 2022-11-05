<?php

namespace Tests\FileManager\File\PermissionTest;

use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\FileManager\File;

test(
    title: 'it should return file\'s permission',
    case: function () {
        $playGround = Address::from_string(root() . 'Tests/PlayGround');
        $regular = $playGround->append('regular');
        File\create($regular->to_string(), 'content');
        chmod($regular->to_string(), 0664);
        assert_true(0664 === File\permission($regular->to_string()));

        $full = $playGround->append('full');
        umask(0);
        File\create($full->to_string(), 'full');
        chmod($full->to_string(), 0777);
        assert_true(0777 === File\permission($full->to_string()));

        return [$regular, $full];
    },
    after: function (Address $regular, Address $full) {
        File\delete($regular->to_string());
        File\delete($full->to_string());
    }
);

test(
    title: 'it should not return cached permission',
    case: function () {
        $playGround = Address::from_string(root() . 'Tests/PlayGround');
        $file = $playGround->append('regular');
        File\create($file->to_string(), 0775);
        umask(0);
        chmod($file->to_string(), 0777);
        assert_true(0777 === File\permission($file->to_string()));
        chmod($file->to_string(), 0666);
        assert_true(0666 === File\permission($file->to_string()));

        return $file;
    },
    after: function (Address $File) {
        File\delete($File->to_string());
    }
);
