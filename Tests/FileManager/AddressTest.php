<?php

namespace Tests\FileManager\AddressTest;

use Saeghe\Saeghe\FileSystem\Address;

test(
    title: 'it should create path from string',
    case: function () {
        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('\user\home/directory     '))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('     \user\home/directory     '))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('\user\home/directory'))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('\user\\\\home//directory'))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('\user\\\\home//directory/'))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'middle-directory' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('\user\home\../middle-directory\directory'))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'middle-directory' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Address('\user\home\.././middle-directory/directory'))->toString()
        );
    }
);

test(
    title: 'it should create path by calling fromString method',
    case: function () {
        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Address::fromString('\user\home/directory')->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Address::fromString('\user\\\\home///directory')->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Address::fromString('\user\\\\home///directory/')->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'middle-directory' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Address::fromString('\user\home\../middle-directory\directory')->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'middle-directory' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Address::fromString('\user\home\.././middle-directory/directory')->toString()
        );
    }
);

test(
    title: 'it should append and return a new path instance',
    case: function () {
        $path = Address::fromString('/user/home');
        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            $path->append('directory')->toString()
            &&
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home'
            ===
            $path->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (Address::fromString('/user/home')->append('\directory'))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (Address::fromString('/user/home')->append('\directory\\'))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            (Address::fromString('\user/home')->append('directory\filename.extension'))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            (Address::fromString('\user/home')->append('directory\filename.extension/'))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            (Address::fromString('\user////home')->append('directory\\\\filename.extension'))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            (Address::fromString('\user/home/..\./')->append('./another-directory/../directory\\\\filename.extension'))->toString()
        );
    }
);

test(
    title: 'it should return new instance of parent directory for the given path',
    case: function () {
        $path = Address::fromString('/user/home/directory/filename.extension');

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            $path->parent()->toString()
            &&
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            $path->toString()
        );
    }
);

test(
    title: 'it should return directory for the given path',
    case: function () {
        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR
            ===
            Address::fromString('/user/home/directory')->directory()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension' . DIRECTORY_SEPARATOR
            ===
            Address::fromString('/user/home/directory/filename.extension')->directory()
        );

        assert(
            DIRECTORY_SEPARATOR
            ===
            Address::fromString('/')->directory()
        );
    }
);
