<?php

namespace Tests\FileManager\Directory\ListTest;

use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\FileManager\Directory;
use Saeghe\Saeghe\FileManager\File;

test(
    title: 'it should return list of files and sub directories in the given directory',
    case: function (Address $directory) {
        assert_true(
            ['sample.txt', 'sub-directory'] === Directory\ls($directory->to_string()),
            'Directory list is not working properly.'
        );

        return $directory;
    },
    before: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/Directory');
        Directory\make($directory->to_string());
        Directory\make($directory->append('sub-directory')->to_string());
        File\create($directory->append('sample.txt')->to_string(), '');
        File\create($directory->append('.hidden.txt')->to_string(), '');

        return $directory;
    },
    after: function (Address $directory) {
        Directory\delete_recursive($directory->to_string());
    }
);

test(
    title: 'it should return empty array when directory is empty',
    case: function (Address $directory) {
        assert_true(
            [] === Directory\ls($directory->to_string()),
            'Directory list is not working properly.'
        );

        return $directory;
    },
    before: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/Directory');
        Directory\make($directory->to_string());

        return $directory;
    },
    after: function (Address $directory) {
        Directory\delete_recursive($directory->to_string());
    }
);
