<?php

namespace Tests\FileManager\Directory\ExistsTest;

use Saeghe\Saeghe\FileManager\Address;
use function Saeghe\Saeghe\FileManager\Directory\delete;
use function Saeghe\Saeghe\FileManager\Directory\delete_recursive;
use function Saeghe\Saeghe\FileManager\Directory\exists;

test(
    title: 'it should return false when directory is not exists',
    case: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/Exists');
        assert_false(exists($directory->to_string()), 'Directory/exists is not working!');
    }
);

test(
    title: 'it should return false when given path is a file',
    case: function (Address $directory) {
        assert_false(exists($directory->to_string()), 'Directory/exists is not working!');

        return $directory;
    },
    before: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/Exists');

        file_put_contents($directory->to_string(), 'A file with directory name');

        return $directory;
    },
    after: function (Address $directory) {
        unlink($directory->to_string());
    }
);

test(
    title: 'it should return true when directory is exist and is a directory',
    case: function (Address $directory) {
        assert_true(exists($directory->to_string()), 'Directory/exists is not working!');

        return $directory;
    },
    before: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/Exists');
        mkdir($directory->to_string());

        return $directory;
    },
    after: function (Address $directory) {
        delete_recursive($directory->to_string());
    }
);

test(
    title: 'it should not return cached value',
    case: function (Address $directory) {
        assert_true(exists($directory->to_string()), 'Directory/exists is not working!');
        delete($directory->to_string());
        assert_false(exists($directory->to_string()), 'Directory/exists is not working!');
    },
    before: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/Exists');
        mkdir($directory->to_string());

        return $directory;
    }
);
