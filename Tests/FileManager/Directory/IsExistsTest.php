<?php

namespace Tests\FileManager\Directory\IsExistsTest;

use Saeghe\Saeghe\Path;
use function Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\FileManager\Directory\exists;

test(
    title: 'it should return false when directory is not exists',
    case: function () {
        $directory = Path::fromString(__DIR__ . '/../../PlayGround/IsExists');
        assert(! exists($directory->toString()), 'Directory/exists is not working!');
    }
);

test(
    title: 'it should return false when given path is a file',
    case: function (Path $directory) {
        assert(! exists($directory->toString()), 'Directory/exists is not working!');

        return $directory;
    },
    before: function () {
        $directory = Path::fromString(__DIR__ . '/../../PlayGround/IsExists');

        file_put_contents($directory->toString(), 'A file with directory name');

        return $directory;
    },
    after: function (Path $directory) {
        unlink($directory->toString());
    }
);

test(
    title: 'it should return false when directory is not exists',
    case: function (Path $directory) {
        assert(exists($directory->toString()), 'Directory/exists is not working!');

        return $directory;
    },
    before: function () {
        $directory = Path::fromString(__DIR__ . '/../../PlayGround/IsExists');
        mkdir($directory->toString());

        return $directory;
    },
    after: function (Path $directory) {
        delete_recursive($directory->toString());
    }
);
