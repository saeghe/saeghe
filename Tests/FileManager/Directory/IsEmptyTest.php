<?php

namespace Tests\FileManager\Directory\IsEmptyTest;

use Saeghe\Saeghe\FileSystem\Address;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\Saeghe\FileManager\Directory\is_empty;

test(
    title: 'it should return true when directory is empty',
    case: function (Address $directory) {
        assert(is_empty($directory->to_string()));

        return $directory;
    },
    before: function () {
        $directory = Address::from_string(__DIR__ . '/Temp');
        mkdir($directory->to_string());
        return $directory;
    },
    after: function (Address $directory) {
        delete_recursive($directory->to_string());
    }
);

test(
    title: 'it should return false when directory has file empty',
    case: function (Address $directory) {
        assert(! is_empty($directory->to_string()));

        return $directory;
    },
    before: function () {
        $directory = Address::from_string(__DIR__ . '/../../PlayGround/IsEmpty');
        mkdir($directory->to_string());
        file_put_contents($directory->append('file.txt')->to_string(), 'content');

        return $directory;
    },
    after: function (Address $directory) {
        delete_recursive($directory->to_string());
    }
);

test(
    title: 'it should return false when directory has sub directory empty',
    case: function (Address $directory) {
        assert(! is_empty($directory->to_string()));

        return $directory;
    },
    before: function () {
        $directory = Address::from_string(__DIR__ . '/../../PlayGround/IsEmpty');
        mkdir($directory->to_string());
        mkdir($directory->append('sub_directory')->to_string());

        return $directory;
    },
    after: function (Address $directory) {
        delete_recursive($directory->to_string());
    }
);
