<?php

namespace Tests\FileManager\Directory\RenewRecursiveTest;

use Saeghe\Saeghe\FileManager\Path;
use Saeghe\Saeghe\FileManager\Directory;
use Saeghe\Saeghe\FileManager\File;

test(
    title: 'it should clean directory when directory exists',
    case: function (Path $directory) {
        Directory\renew_recursive($directory->stringify());
        assert_true(Directory\exists($directory->stringify()));
        assert_false(File\exists($directory->append('file.txt')->stringify()));

        return $directory;
    },
    before: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/Renew/Recursive');
        Directory\make_recursive($directory->stringify());
        file_put_contents($directory->append('file.txt')->stringify(), 'content');

        return $directory;
    },
    after: function (Path $directory) {
        Directory\delete_recursive($directory->parent()->stringify());
    }
);

test(
    title: 'it should create the directory recursively when directory not exists',
    case: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/Renew/Recursive');

        Directory\renew_recursive($directory->stringify());
        assert_true(Directory\exists($directory->parent()->stringify()));
        assert_true(Directory\exists($directory->stringify()));

        return $directory;
    },
    after: function (Path $directory) {
        Directory\delete_recursive($directory->parent()->stringify());
    }
);
