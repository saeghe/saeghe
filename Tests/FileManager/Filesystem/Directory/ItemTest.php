<?php

namespace Tests\FileManager\Filesystem\Directory\ItemTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use Saeghe\Saeghe\FileManager\Filesystem\File;
use Saeghe\Saeghe\FileManager\Filesystem\Symlink;

test(
    title: 'it should return correspond object for the item',
    case: function (Directory $directory) {
        assert_true($directory->item('.hidden.txt')->stringify() === $directory->file('.hidden.txt')->stringify());
        assert_true($directory->item('.hidden.txt') instanceof File);
        assert_true($directory->item('sample.txt')->stringify() === $directory->file('sample.txt')->stringify());
        assert_true($directory->item('sample.txt') instanceof File);
        assert_true($directory->item('sub-directory')->stringify() === $directory->subdirectory('sub-directory')->stringify());
        assert_true($directory->item('sub-directory') instanceof Directory);
        assert_true($directory->item('symlink')->stringify() === $directory->symlink('symlink')->stringify());
        assert_true($directory->item('symlink') instanceof Symlink);

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
