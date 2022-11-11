<?php

namespace Tests\FileManager\Filesystem\Directory\ListTest;

use Saeghe\Saeghe\FileManager\Filesystem\Directory;
use Saeghe\Saeghe\FileManager\Filesystem\File;
use Saeghe\Saeghe\FileManager\Filesystem\FilesystemCollection;
use Saeghe\Saeghe\FileManager\Filesystem\Symlink;

test(
    title: 'it should return files and directories in the directory',
    case: function (Directory $source, Directory $directory, File $file, Symlink $symlink) {
        [$expectedDirectory, $expectedFile, $expectedSymlink] = $source->ls();

        assert_true($source->ls() instanceof FilesystemCollection);
        assert_true($expectedDirectory instanceof Directory, 'Directory type does not detected');
        assert_true($expectedDirectory->path->string() === $directory->path->string(), 'Directory not passed');
        assert_true($expectedFile instanceof File, 'File type does not detected');
        assert_true($expectedFile->path->string() === $file->path->string(), 'File not passed');
        assert_true($expectedSymlink instanceof Symlink, 'Symlink type does not detected');
        assert_true($expectedSymlink->path->string() === $symlink->path->string(), 'Symlink not passed');

        return [$directory, $file, $symlink];
    },
    before: function () {
        $source = Directory::from_string(root() . 'Tests/PlayGround');
        $directory = Directory::from_string(root() . 'Tests/PlayGround/DirectoryAddress')->make();
        $file = File::from_string(root() . 'Tests/PlayGround/File')->create('content');
        $symlink = Symlink::from_string(root() . 'Tests/PlayGround/Symlink')->link($file);

        return [$source, $directory, $file, $symlink];
    },
    after: function(Directory $directory, File $file, Symlink $symlink) {
        $symlink->delete();
        $file->delete();
        $directory->delete();
    }
);
