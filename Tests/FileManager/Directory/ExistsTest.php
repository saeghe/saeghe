<?php

namespace Tests\FileManager\Directory\ExistsTest;

use Saeghe\Saeghe\FileManager\Path;
use function Saeghe\Saeghe\FileManager\Directory\delete;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\Saeghe\FileManager\Directory\exists;

test(
    title: 'it should return false when directory is not exists',
    case: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/Exists');
        assert_false(exists($directory->stringify()), 'Directory/exists is not working!');
    }
);

test(
    title: 'it should return false when given path is a file',
    case: function (Path $directory) {
        assert_false(exists($directory->stringify()), 'Directory/exists is not working!');

        return $directory;
    },
    before: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/Exists');

        file_put_contents($directory->stringify(), 'A file with directory name');

        return $directory;
    },
    after: function (Path $directory) {
        unlink($directory->stringify());
    }
);

test(
    title: 'it should return true when directory is exist and is a directory',
    case: function (Path $directory) {
        assert_true(exists($directory->stringify()), 'Directory/exists is not working!');

        return $directory;
    },
    before: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/Exists');
        mkdir($directory->stringify());

        return $directory;
    },
    after: function (Path $directory) {
        delete_recursive($directory->stringify());
    }
);

test(
    title: 'it should not return cached value',
    case: function (Path $directory) {
        assert_true(exists($directory->stringify()), 'Directory/exists is not working!');
        delete($directory->stringify());
        assert_false(exists($directory->stringify()), 'Directory/exists is not working!');
    },
    before: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/Exists');
        mkdir($directory->stringify());

        return $directory;
    }
);
