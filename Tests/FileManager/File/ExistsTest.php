<?php

namespace Tests\FileManager\File\ExistsTest;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\File\exists;
use function Saeghe\Saeghe\FileManager\Directory\clean;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;
use function Saeghe\TestRunner\Assertions\Boolean\assert_false;

test(
    title: 'it should return false when file is not exists',
    case: function () {
        $file = Path::from_string(root() . 'Tests/PlayGround/IsExists');
        assert_false(exists($file), 'File/exists is not working!');
    }
);

test(
    title: 'it should return false when given path is a directory',
    case: function (Path $file) {
        assert_false(exists($file), 'File/exists is not working!');

        return $file;
    },
    before: function () {
        $file = Path::from_string(root() . 'Tests/PlayGround/file');

        mkdir($file);

        return $file;
    },
    after: function (Path $file) {
        clean($file->parent());
    }
);

test(
    title: 'it should return true when file is exist and is a file',
    case: function (Path $file) {
        assert_true(exists($file), 'File/exists is not working!');

        return $file;
    },
    before: function () {
        $file = Path::from_string(root() . 'Tests/PlayGround/File');
        file_put_contents($file, 'content');

        return $file;
    },
    after: function (Path $file) {
        clean($file->parent());
    }
);
