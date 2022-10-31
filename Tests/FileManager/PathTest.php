<?php

namespace Tests\FileManager\PathTest;

use function Saeghe\Saeghe\FileManager\Path\realpath;

test(
    title: 'it should return real path for the given path',
    case: function () {
        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home'
            ===
            realpath('/user/home            ')
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home'
            ===
            realpath('           /user/home            ')
        );
        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            realpath('/user/home/./directory/filename.extension')
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            realpath('/user/home/./directory/another-directory/../filename.extension')
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            realpath('/user/home\\\\/./directory///another-directory//../filename.extension')
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            realpath('/user/home/directory/../../filename.extension')
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            realpath('\user\home/directory')
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            realpath('\user\\\\home////directory')
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            realpath('\user\\\\home////directory\\')
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'directory'
            ===
            realpath('\user\home\..\directory')
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'directory'
            ===
            realpath('\user\home\.././directory')
        );
    }
);
