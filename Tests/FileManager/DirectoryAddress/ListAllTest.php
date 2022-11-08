<?php

namespace Tests\FileManager\DirectoryAddress\ListAllTest;

use Saeghe\Saeghe\FileManager\DirectoryAddress;

test(
    title: 'it should return list of files and sub directories in the given directory contain hidden files',
    case: function (DirectoryAddress $directory) {
        $results = $directory->ls_all();

        assert_true($results[0]->to_string() === $directory->file('.hidden.txt')->to_string());
        assert_true($results[1]->to_string() === $directory->file('sample.txt')->to_string());
        assert_true($results[2]->to_string() === $directory->file('sub-directory')->to_string());
        assert_true($results[3]->to_string() === $directory->file('symlink')->to_string());

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

test(
    title: 'it should return empty array when directory is empty',
    case: function (DirectoryAddress $directory) {
        assert_true(
            [] === $directory->ls_all(),
            'Directory list all is not working properly.'
        );

        return $directory;
    },
    before: function () {
        $directory = DirectoryAddress::from_string(root() . 'Tests/PlayGround/Directory');
        $directory->make();

        return $directory;
    },
    after: function (DirectoryAddress $directory) {
        $directory->delete_recursive();
    }
);
