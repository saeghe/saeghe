<?php

namespace Tests\FileManager\Directory\IsEmptyTest;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\Saeghe\FileManager\Directory\is_empty;

test(
    title: 'it should return true when directory is empty',
    case: function (Path $directory) {
        assert_true(is_empty($directory->stringify()));

        return $directory;
    },
    before: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/Temp');
        mkdir($directory->stringify());
        return $directory;
    },
    after: function (Path $directory) {
        delete_recursive($directory->stringify());
    }
);

test(
    title: 'it should return false when directory has file empty',
    case: function (Path $directory) {
        assert_false(is_empty($directory->stringify()));

        return $directory;
    },
    before: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/IsEmpty');
        mkdir($directory->stringify());
        file_put_contents($directory->append('file.txt')->stringify(), 'content');

        return $directory;
    },
    after: function (Path $directory) {
        delete_recursive($directory->stringify());
    }
);

test(
    title: 'it should return false when directory has sub directory empty',
    case: function (Path $directory) {
        assert_false(is_empty($directory->stringify()));

        return $directory;
    },
    before: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/IsEmpty');
        mkdir($directory->stringify());
        mkdir($directory->append('sub_directory')->stringify());

        return $directory;
    },
    after: function (Path $directory) {
        delete_recursive($directory->stringify());
    }
);
