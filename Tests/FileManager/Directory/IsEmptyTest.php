<?php

namespace Tests\FileManager\Directory\IsEmptyTest;

use Saeghe\Saeghe\FileSystem\Address;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\Saeghe\FileManager\Directory\is_empty;

test(
    title: 'it should return true when directory is empty',
    case: function (Address $directory) {
        assert(is_empty($directory->toString()));

        return $directory;
    },
    before: function () {
        $directory = Address::fromString(__DIR__ . '/Temp');
        mkdir($directory->toString());
        return $directory;
    },
    after: function (Address $directory) {
        delete_recursive($directory->toString());
    }
);

test(
    title: 'it should return false when directory has file empty',
    case: function (Address $directory) {
        assert(! is_empty($directory->toString()));

        return $directory;
    },
    before: function () {
        $directory = Address::fromString(__DIR__ . '/../../PlayGround/IsEmpty');
        mkdir($directory->toString());
        file_put_contents($directory->append('file.txt')->toString(), 'content');

        return $directory;
    },
    after: function (Address $directory) {
        delete_recursive($directory->toString());
    }
);

test(
    title: 'it should return false when directory has sub directory empty',
    case: function (Address $directory) {
        assert(! is_empty($directory->toString()));

        return $directory;
    },
    before: function () {
        $directory = Address::fromString(__DIR__ . '/../../PlayGround/IsEmpty');
        mkdir($directory->toString());
        mkdir($directory->append('sub_directory')->toString());

        return $directory;
    },
    after: function (Address $directory) {
        delete_recursive($directory->toString());
    }
);
