<?php

namespace Tests\PathTest;

use Saeghe\Saeghe\Path;

require_once __DIR__ . '/../Source/Path.php';
require_once __DIR__ . '/../Source/Str.php';

test(
    title: 'it should create path from string',
    case: function () {
        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Path('\user\home/directory'))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Path('\user\\\\home//directory'))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Path('\user\\\\home//directory/'))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'middle-directory' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Path('\user\home\../middle-directory\directory'))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'middle-directory' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (new Path('\user\home\.././middle-directory/directory'))->toString()
        );
    }
);

test(
    title: 'it should create path by calling fromString method',
    case: function () {
        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Path::fromString('\user\home/directory')->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Path::fromString('\user\\\\home///directory')->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Path::fromString('\user\\\\home///directory/')->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'middle-directory' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Path::fromString('\user\home\../middle-directory\directory')->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'middle-directory' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Path::fromString('\user\home\.././middle-directory/directory')->toString()
        );
    }
);

test(
    title: 'it should return path by from replaceSeparator method',
    case: function () {
        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Path::replaceSeparator('\user\home/directory')
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Path::replaceSeparator('\user\\\\home////directory')
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR
            ===
            Path::replaceSeparator('\user\\\\home////directory\\')
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Path::replaceSeparator('\user\home\..\directory')
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.' . DIRECTORY_SEPARATOR . 'directory'
            ===
            Path::replaceSeparator('\user\home\.././directory')
        );
    }
);

test(
    title: 'it should append and return a new path instance',
    case: function () {
        $path = Path::fromString('/user/home');
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
            (Path::fromString('/user/home')->append('\directory'))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory'
            ===
            (Path::fromString('/user/home')->append('\directory\\'))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            (Path::fromString('\user/home')->append('directory\filename.extension'))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            (Path::fromString('\user/home')->append('directory\filename.extension/'))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            (Path::fromString('\user////home')->append('directory\\\\filename.extension'))->toString()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            (Path::fromString('\user/home/..\./')->append('./another-directory/../directory\\\\filename.extension'))->toString()
        );
    }
);

test(
    title: 'it should return new instance of parent directory for the given path',
    case: function () {
        $path = Path::fromString('/user/home/directory/filename.extension');

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
    title: 'it should return real path for the given path',
    case: function () {
        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            Path::realPath('/user/home/./directory/filename.extension')
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            Path::realPath('/user/home/./directory/another-directory/../filename.extension')
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            Path::realPath('/user/home\\\\/./directory///another-directory//../filename.extension')
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'filename.extension'
            ===
            Path::realPath('/user/home/directory/../../filename.extension')
        );
    }
);

test(
    title: 'it should return directory for the given path',
    case: function () {
        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR
            ===
            Path::fromString('/user/home/directory')->directory()
        );

        assert(
            DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'directory' . DIRECTORY_SEPARATOR . 'filename.extension' . DIRECTORY_SEPARATOR
            ===
            Path::fromString('/user/home/directory/filename.extension')->directory()
        );

        assert(
            DIRECTORY_SEPARATOR
            ===
            Path::fromString('/')->directory()
        );
    }
);
