<?php

namespace Tests\FileManager\DirectoryAddress\ItemTest;

use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\FileManager\DirectoryAddress;
use Saeghe\Saeghe\FileManager\FileAddress;
use Saeghe\Saeghe\FileManager\SymlinkAddress;

test(
    title: 'it should return correspond object for the item',
    case: function (DirectoryAddress $directory) {
        assert_true($directory->item('.hidden.txt')->to_string() === $directory->file('.hidden.txt')->to_string());
        assert_true($directory->item('.hidden.txt') instanceof FileAddress);
        assert_true($directory->item('sample.txt')->to_string() === $directory->file('sample.txt')->to_string());
        assert_true($directory->item('sample.txt') instanceof FileAddress);
        assert_true($directory->item('sub-directory')->to_string() === $directory->subdirectory('sub-directory')->to_string());
        assert_true($directory->item('sub-directory') instanceof DirectoryAddress);
        assert_true($directory->item('symlink')->to_string() === $directory->symlink('symlink')->to_string());
        assert_true($directory->item('symlink') instanceof SymlinkAddress);
        assert_true($directory->item('not-exists')->to_string() === $directory->append('not-exists')->to_string());
        assert_true($directory->item('not-exists') instanceof Address);

        return $directory;
    },
    before: function () {
        $directory = DirectoryAddress::from_string(root() . 'Tests/PlayGround/Directory');
        $directory->make();
        $directory->append('sub-directory')->make();
        $directory->file('sample.txt')->create('');
        $directory->file('.hidden.txt')->create('');
        $directory->symlink('symlink')->link($directory->file('sample.txt'));

        return $directory;
    },
    after: function (DirectoryAddress $directory) {
        $directory->delete_recursive();
    }
);
