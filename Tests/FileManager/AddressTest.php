<?php

namespace Tests\FileManager\AddressTest;

use Saeghe\Saeghe\FileManager\Address;
use Saeghe\Saeghe\FileManager\Directory;
use Saeghe\Saeghe\FileManager\File;
use Saeghe\Saeghe\FileManager\Symlink;

test(
    title: 'it should create path from string',
    case: function () {
        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('\user\home/directory     '))->to_string()
        );

        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('     \user\home/directory     '))->to_string()
        );

        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('\user\home/directory'))->to_string()
        );

        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('\user\\\\home//directory'))->to_string()
        );

        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('\user\\\\home//directory/'))->to_string()
        );

        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'middle-directory' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('\user\home\../middle-directory\directory'))->to_string()
        );

        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'middle-directory' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('\user\home\.././middle-directory/directory'))->to_string()
        );
    }
);

test(
    title: 'it should create path by calling fromString method',
    case: function () {
        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Address::from_string('\user\home/directory')->to_string()
        );

        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Address::from_string('\user\\\\home///directory')->to_string()
        );

        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Address::from_string('\user\\\\home///directory/')->to_string()
        );

        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'middle-directory' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Address::from_string('\user\home\../middle-directory\directory')->to_string()
        );

        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'middle-directory' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Address::from_string('\user\home\.././middle-directory/directory')->to_string()
        );
    }
);

test(
    title: 'it should append and return a new path instance',
    case: function () {
        $path = Address::from_string('/user/home');
        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            $path->append('directory')->to_string()
            &&
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home'
            ===
            $path->to_string()
        );

        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (Address::from_string('/user/home')->append('\directory'))->to_string()
        );

        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (Address::from_string('/user/home')->append('\directory\\'))->to_string()
        );

        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            (Address::from_string('\user/home')->append('directory\filename.extension'))->to_string()
        );

        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            (Address::from_string('\user/home')->append('directory\filename.extension/'))->to_string()
        );

        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            (Address::from_string('\user////home')->append('directory\\\\filename.extension'))->to_string()
        );

        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            (Address::from_string('\user/home/..\./')->append('./another-directory/../directory\\\\filename.extension'))->to_string()
        );
    }
);

test(
    title: 'it should return new instance of parent directory for the given path',
    case: function () {
        $path = Address::from_string('/user/home/directory/filename.extension');

        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            $path->parent()->to_string()
            &&
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            $path->to_string()
        );
    }
);

test(
    title: 'it should return directory for the given path',
    case: function () {
        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR
            ===
            Address::from_string('/user/home/directory')->directory()
        );

        assert_true(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension' . DIRECTORY_SEPARATOR
            ===
            Address::from_string('/user/home/directory/filename.extension')->directory()
        );

        assert_true(
            DIRECTORY_SEPARATOR
            ===
            Address::from_string('/')->directory()
        );
    }
);

test(
    title: 'it should check if the given file exists',
    case: function () {
        assert_true(Address::from_string(__FILE__)->exists());
        assert_false(Address::from_string(__FILE__)->append('not_exists.txt')->exists());
    }
);

test(
    title: 'it should detect the leaf',
    case: function () {
        assert_true(Address::from_string('/')->to_string() === Address::from_string('/')->leaf());
        assert_true('AddressTest.php' === Address::from_string(__FILE__)->leaf());
        assert_true('FileManager' === Address::from_string(__DIR__)->leaf());
    }
);

test(
    title: 'it should detect when address is a directory',
    case: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/Directory');
        Directory\make($directory->to_string());
        $file = Address::from_string(root() . 'Tests/PlayGround/File');
        File\create($file->to_string(), 'file content');
        $link = Address::from_string(root() . 'Tests/PlayGround/Symlink');
        Symlink\link($file->to_string(), $link->to_string());

        assert_true($directory->is_directory());
        assert_false($directory->is_file());
        assert_false($directory->is_symlink());

        return[$directory, $file, $link];
    },
    after: function (Address $directory, Address $file, Address $link) {
        Symlink\delete($link->to_string());
        File\delete($file->to_string());
        Directory\delete($directory->to_string());
    }
);

test(
    title: 'it should detect when address is a file',
    case: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/Directory');
        Directory\make($directory->to_string());
        $file = Address::from_string(root() . 'Tests/PlayGround/File');
        File\create($file->to_string(), 'file content');
        $link = Address::from_string(root() . 'Tests/PlayGround/Symlink');
        Symlink\link($file->to_string(), $link->to_string());

        assert_false($file->is_directory());
        assert_true($file->is_file());
        assert_false($file->is_symlink());

        return[$directory, $file, $link];
    },
    after: function (Address $directory, Address $file, Address $link) {
        unlink($link->to_string());
        File\delete($file->to_string());
        Directory\delete($directory->to_string());
    }
);

test(
    title: 'it should detect when address is a symlink',
    case: function () {
        $directory = Address::from_string(root() . 'Tests/PlayGround/Directory');
        Directory\make($directory->to_string());
        $file = Address::from_string(root() . 'Tests/PlayGround/File');
        File\create($file->to_string(), 'file content');
        $link = Address::from_string(root() . 'Tests/PlayGround/Symlink');
        Symlink\link($file->to_string(), $link->to_string());

        assert_false($link->is_directory());
        assert_true($link->is_file());
        assert_true($link->is_symlink());

        return[$directory, $file, $link];
    },
    after: function (Address $directory, Address $file, Address $link) {
        Symlink\delete($link->to_string());
        File\delete($file->to_string());
        Directory\delete($directory->to_string());
    }
);
