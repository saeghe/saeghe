<?php

namespace Tests\FileManager\DirectoryAddress\ListTest;

use Saeghe\Saeghe\FileManager\DirectoryAddress;
use Saeghe\Saeghe\FileManager\FileAddress;
use Saeghe\Saeghe\FileManager\SymlinkAddress;

test(
    title: 'it should return files and directories in the directory',
    case: function (DirectoryAddress $source, DirectoryAddress $directory, FileAddress $file, SymlinkAddress $symlink) {
        [$expectedDirectory, $expectedFile, $expectedSymlink] = $source->ls();

        assert_true($expectedDirectory instanceof DirectoryAddress, 'Directory type does not detected');
        assert_true($expectedDirectory->to_string() === $directory->to_string(), 'Directory not passed');
        assert_true($expectedFile instanceof FileAddress, 'File type does not detected');
        assert_true($expectedFile->to_string() === $file->to_string(), 'File not passed');
        assert_true($expectedSymlink instanceof SymlinkAddress, 'Symlink type does not detected');
        assert_true($expectedSymlink->to_string() === $symlink->to_string(), 'Symlink not passed');

        return [$directory, $file, $symlink];
    },
    before: function () {
        $source = DirectoryAddress::from_string(root() . 'Tests/PlayGround');
        $directory = DirectoryAddress::from_string(root() . 'Tests/PlayGround/DirectoryAddress')->make();
        $file = FileAddress::from_string(root() . 'Tests/PlayGround/File')->create('content');
        $symlink = SymlinkAddress::from_string(root() . 'Tests/PlayGround/Symlink')->link($file);

        return [$source, $directory, $file, $symlink];
    },
    after: function(DirectoryAddress $directory, FileAddress $file, SymlinkAddress $symlink) {
        $symlink->delete();
        $file->delete();
        $directory->delete();
    }
);
