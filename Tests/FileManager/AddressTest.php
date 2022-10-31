<?php

namespace Tests\FileManager\AddressTest;

use Saeghe\Saeghe\FileSystem\Address;

test(
    title: 'it should create path from string',
    case: function () {
        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('\user\home/directory     '))->to_string()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('     \user\home/directory     '))->to_string()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('\user\home/directory'))->to_string()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('\user\\\\home//directory'))->to_string()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('\user\\\\home//directory/'))->to_string()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'middle-directory' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('\user\home\../middle-directory\directory'))->to_string()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'middle-directory' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('\user\home\.././middle-directory/directory'))->to_string()
        );
    }
);

test(
    title: 'it should create path by calling fromString method',
    case: function () {
        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Address::from_string('\user\home/directory')->to_string()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Address::from_string('\user\\\\home///directory')->to_string()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Address::from_string('\user\\\\home///directory/')->to_string()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'middle-directory' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Address::from_string('\user\home\../middle-directory\directory')->to_string()
        );

        assert(
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
        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            $path->append('directory')->to_string()
            &&
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home'
            ===
            $path->to_string()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (Address::from_string('/user/home')->append('\directory'))->to_string()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (Address::from_string('/user/home')->append('\directory\\'))->to_string()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            (Address::from_string('\user/home')->append('directory\filename.extension'))->to_string()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            (Address::from_string('\user/home')->append('directory\filename.extension/'))->to_string()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            (Address::from_string('\user////home')->append('directory\\\\filename.extension'))->to_string()
        );

        assert(
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

        assert(
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
        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR
            ===
            Address::from_string('/user/home/directory')->directory()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension' . DIRECTORY_SEPARATOR
            ===
            Address::from_string('/user/home/directory/filename.extension')->directory()
        );

        assert(
            DIRECTORY_SEPARATOR
            ===
            Address::from_string('/')->directory()
        );
    }
);
