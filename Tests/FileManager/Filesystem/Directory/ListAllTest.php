<?php

namespace Tests\FileManager\Filesystem\Directory\ListAllTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use Saeghe\Saeghe\FileManager\Filesystem\FilesystemCollection;

test(
    title: 'it should return list of files and sub directories in the given directory contain hidden files',
    case: function (Directory $directory) {
        $results = $directory->ls_all();

        assert_true($directory->ls_all() instanceof FilesystemCollection);
        assert_true($results[0]->stringify() === $directory->file('.hidden.txt')->stringify());
        assert_true($results[1]->stringify() === $directory->file('sample.txt')->stringify());
        assert_true($results[2]->stringify() === $directory->file('sub-directory')->stringify());
        assert_true($results[3]->stringify() === $directory->file('symlink')->stringify());

        return $directory;
    },
    before: function () {
        $directory = new Directory(root() . 'Tests/PlayGround/Directory');
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
        $directory = new Directory(root() . 'Tests/PlayGround/Directory');
        $directory->make();

        return $directory;
    },
    after: function (Directory $directory) {
        $directory->delete_recursive();
    }
);
