<?php

namespace Tests\FileManager\Filesystem\Directory\ListAllTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use Saeghe\Saeghe\FileManager\Filesystem\FilesystemCollection;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should return list of files and sub directories in the given directory contain hidden files',
    case: function (Directory $directory) {
        $results = $directory->ls_all();

        assert_true($directory->ls_all() instanceof FilesystemCollection);
        assert_true($results[0]->path->string() === $directory->file('.hidden.txt')->path->string());
        assert_true($results[1]->path->string() === $directory->file('sample.txt')->path->string());
        assert_true($results[2]->path->string() === $directory->file('sub-directory')->path->string());
        assert_true($results[3]->path->string() === $directory->file('symlink')->path->string());

        return $directory;
    },
    before: function () {
        $directory = Directory::from_string(root() . 'Tests/PlayGround/Directory');
        $directory->make();
        $directory->subdirectory('sub-directory')->make();
        $directory->file('sample.txt')->create('');
        $directory->file('.hidden.txt')->create('');
        $directory->symlink('symlink')->link($directory->file('sample.txt'));

        return $directory;
    },
    after: function (Directory $directory) {
        $directory->delete_recursive();
    }
);

test(
    title: 'it should return empty array when directory is empty',
    case: function (Directory $directory) {
        assert_true(
            [] === $directory->ls_all()->items(),
            'Directory list all is not working properly.'
        );

        return $directory;
    },
    before: function () {
        $directory = Directory::from_string(root() . 'Tests/PlayGround/Directory');
        $directory->make();

        return $directory;
    },
    after: function (Directory $directory) {
        $directory->delete_recursive();
    }
);
