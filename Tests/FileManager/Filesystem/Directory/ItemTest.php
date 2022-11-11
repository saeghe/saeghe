<?php

namespace Tests\FileManager\Filesystem\Directory\ItemTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use Saeghe\Saeghe\FileManager\Filesystem\File;
use Saeghe\Saeghe\FileManager\Filesystem\Symlink;

test(
    title: 'it should return correspond object for the item',
    case: function (Directory $directory) {
        assert_true($directory->item('.hidden.txt')->path->string() === $directory->file('.hidden.txt')->path->string());
        assert_true($directory->item('.hidden.txt') instanceof File);
        assert_true($directory->item('sample.txt')->path->string() === $directory->file('sample.txt')->path->string());
        assert_true($directory->item('sample.txt') instanceof File);
        assert_true($directory->item('sub-directory')->path->string() === $directory->subdirectory('sub-directory')->path->string());
        assert_true($directory->item('sub-directory') instanceof Directory);
        assert_true($directory->item('symlink')->path->string() === $directory->symlink('symlink')->path->string());
        assert_true($directory->item('symlink') instanceof Symlink);

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
