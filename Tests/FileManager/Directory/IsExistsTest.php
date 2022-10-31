<?php

namespace Tests\FileManager\Directory\IsExistsTest;

use Saeghe\Saeghe\FileSystem\Address;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\Saeghe\FileManager\Directory\exists;

test(
    title: 'it should return false when directory is not exists',
    case: function () {
        $directory = Address::from_string(__DIR__ . '/../../PlayGround/IsExists');
        assert(! exists($directory->to_string()), 'Directory/exists is not working!');
    }
);

test(
    title: 'it should return false when given path is a file',
    case: function (Address $directory) {
        assert(! exists($directory->to_string()), 'Directory/exists is not working!');

        return $directory;
    },
    before: function () {
        $directory = Address::from_string(__DIR__ . '/../../PlayGround/IsExists');

        file_put_contents($directory->to_string(), 'A file with directory name');

        return $directory;
    },
    after: function (Address $directory) {
        unlink($directory->to_string());
    }
);

test(
    title: 'it should return false when directory is not exists',
    case: function (Address $directory) {
        assert(exists($directory->to_string()), 'Directory/exists is not working!');

        return $directory;
    },
    before: function () {
        $directory = Address::from_string(__DIR__ . '/../../PlayGround/IsExists');
        mkdir($directory->to_string());

        return $directory;
    },
    after: function (Address $directory) {
        delete_recursive($directory->to_string());
    }
);
