<?php

namespace Tests\FileManager\Directory\ExistsOrCreate;

use Saeghe\Saeghe\FileManager\Path;
use Saeghe\Saeghe\FileManager\Directory;

test(
    title: 'it should return true when directory exists',
    case: function (Path $directory) {
        assert_true(Directory\exists_or_create($directory));

        return $directory;
    },
    before: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/ExistsOrCreate');
        Directory\make($directory);

        return $directory;
    },
    after: function (Path $directory) {
        Directory\delete($directory);
    }
);

test(
    title: 'it should create and return true when directory not exists',
    case: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/ExistsOrCreate');

        assert_true(Directory\exists_or_create($directory));
        assert_true(Directory\exists($directory));

        return $directory;
    },
    after: function (Path $directory) {
        Directory\delete($directory);
    }
);
