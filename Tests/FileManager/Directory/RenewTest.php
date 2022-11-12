<?php

namespace Tests\FileManager\Directory\RenewTest;

use Saeghe\Saeghe\FileManager\Path;
use Saeghe\Saeghe\FileManager\Directory;
use Saeghe\Saeghe\FileManager\File;

test(
    title: 'it should clean directory when directory exists',
    case: function (Path $directory) {
        Directory\renew($directory);
        assert_true(Directory\exists($directory));
        assert_false(File\exists($directory->append('file.txt')));

        return $directory;
    },
    before: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/Renew');
        Directory\make($directory);
        file_put_contents($directory->append('file.txt'), 'content');

        return $directory;
    },
    after: function (Path $directory) {
        Directory\delete_recursive($directory);
    }
);

test(
    title: 'it should create the directory when directory not exists',
    case: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/Renew');

        Directory\renew($directory);
        assert_true(Directory\exists($directory));

        return $directory;
    },
    after: function (Path $directory) {
        Directory\delete_recursive($directory);
    }
);
