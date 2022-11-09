<?php

namespace Tests\FileManager\Directory\ListAllTest;

use Saeghe\Saeghe\FileManager\Path;
use Saeghe\Saeghe\FileManager\Directory;
use Saeghe\Saeghe\FileManager\File;

test(
    title: 'it should return list of files and sub directories in the given directory contain hidden files',
    case: function (Path $directory) {
        assert_true(
            ['.hidden.txt', 'sample.txt', 'sub-directory'] === Directory\ls_all($directory->stringify()),
            'Directory list all is not working properly.'
        );

        return $directory;
    },
    before: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/Directory');
        Directory\make($directory->stringify());
        Directory\make($directory->append('sub-directory')->stringify());
        File\create($directory->append('sample.txt')->stringify(), '');
        File\create($directory->append('.hidden.txt')->stringify(), '');

        return $directory;
    },
    after: function (Path $directory) {
        Directory\delete_recursive($directory->stringify());
    }
);

test(
    title: 'it should return empty array when directory is empty',
    case: function (Path $directory) {
        assert_true(
            [] === Directory\ls_all($directory->stringify()),
            'Directory list all is not working properly.'
        );

        return $directory;
    },
    before: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/Directory');
        Directory\make($directory->stringify());

        return $directory;
    },
    after: function (Path $directory) {
        Directory\delete_recursive($directory->stringify());
    }
);
