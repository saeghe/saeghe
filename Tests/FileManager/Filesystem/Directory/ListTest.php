<?php

namespace Tests\FileManager\Filesystem\Directory\ListTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use Saeghe\Saeghe\FileManager\Filesystem\File;
use Saeghe\Saeghe\FileManager\Filesystem\Symlink;

test(
    title: 'it should return files and directories in the directory',
    case: function (Directory $source, Directory $directory, File $file, Symlink $symlink) {
        [$expectedDirectory, $expectedFile, $expectedSymlink] = $source->ls();

        assert_true($expectedDirectory instanceof Directory, 'Directory type does not detected');
        assert_true($expectedDirectory->stringify() === $directory->stringify(), 'Directory not passed');
        assert_true($expectedFile instanceof File, 'File type does not detected');
        assert_true($expectedFile->stringify() === $file->stringify(), 'File not passed');
        assert_true($expectedSymlink instanceof Symlink, 'Symlink type does not detected');
        assert_true($expectedSymlink->stringify() === $symlink->stringify(), 'Symlink not passed');

        return [$directory, $file, $symlink];
    },
    before: function () {
        $source = new Directory(root() . 'Tests/PlayGround');
        $directory = (new Directory(root() . 'Tests/PlayGround/DirectoryAddress'))->make();
        $file = (new File(root() . 'Tests/PlayGround/File'))->create('content');
        $symlink = (new Symlink(root() . 'Tests/PlayGround/Symlink'))->link($file);

        return [$source, $directory, $file, $symlink];
    },
    after: function(Directory $directory, File $file, Symlink $symlink) {
        $symlink->delete();
        $file->delete();
        $directory->delete();
    }
);
