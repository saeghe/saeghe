<?php

namespace Tests\FileManager\Directory\RenewRecursiveTest;

use Saeghe\Saeghe\FileManager\Path;
use Saeghe\Saeghe\FileManager\Directory;
use Saeghe\Saeghe\FileManager\File;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;
use function Saeghe\TestRunner\Assertions\Boolean\assert_false;

test(
    title: 'it should clean directory when directory exists',
    case: function (Path $directory) {
        Directory\renew_recursive($directory);
        assert_true(Directory\exists($directory));
        assert_false(File\exists($directory->append('file.txt')));

        return $directory;
    },
    before: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/Renew/Recursive');
        Directory\make_recursive($directory);
        file_put_contents($directory->append('file.txt'), 'content');

        return $directory;
    },
    after: function (Path $directory) {
        Directory\delete_recursive($directory->parent());
    }
);

test(
    title: 'it should create the directory recursively when directory not exists',
    case: function () {
        $directory = Path::from_string(root() . 'Tests/PlayGround/Renew/Recursive');

        Directory\renew_recursive($directory);
        assert_true(Directory\exists($directory->parent()));
        assert_true(Directory\exists($directory));

        return $directory;
    },
    after: function (Path $directory) {
        Directory\delete_recursive($directory->parent());
    }
);
