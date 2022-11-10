<?php

spl_autoload_register(function ($class) {
    $classMap = [
        'Saeghe\Saeghe\Exception\CredentialCanNotBeSetException' => \realpath(__DIR__ . '/Source/Exception/CredentialCanNotBeSetException.php'),
        'Saeghe\Saeghe\Git\Repository' => \realpath(__DIR__ . '/Source/Git/Repository.php'),
        'Saeghe\Saeghe\Git\Exception\InvalidTokenException' => \realpath(__DIR__ . '/Source/Git/Exception/InvalidTokenException.php'),
        'Saeghe\Saeghe\Config' => \realpath(__DIR__ . '/Source/Config.php'),
        'Saeghe\Saeghe\FileManager\Path' => \realpath(__DIR__ . '/Source/FileManager/Path.php'),
        'Saeghe\Saeghe\FileManager\Filesystem\Address' => \realpath(__DIR__ . '/Source/FileManager/Filesystem/Address.php'),
        'Saeghe\Saeghe\FileManager\Filesystem\Directory' => \realpath(__DIR__ . '/Source/FileManager/Filesystem/Directory.php'),
        'Saeghe\Saeghe\FileManager\Filesystem\File' => \realpath(__DIR__ . '/Source/FileManager/Filesystem/File.php'),
        'Saeghe\Saeghe\FileManager\Filesystem\Symlink' => \realpath(__DIR__ . '/Source/FileManager/Filesystem/Symlink.php'),
        'Saeghe\Saeghe\Meta' => \realpath(__DIR__ . '/Source/Meta.php'),
        'Saeghe\Saeghe\Package' => \realpath(__DIR__ . '/Source/Package.php'),
        'Saeghe\Saeghe\Project' => \realpath(__DIR__ . '/Source/Project.php'),
    ];

    require_once $classMap[$class];
});

require \realpath(__DIR__ . '/Packages/saeghe/cli/Source/IO/Read.php');
require \realpath(__DIR__ . '/Packages/saeghe/cli/Source/IO/Write.php');
require \realpath(__DIR__ . '/Source/DataType/Arr.php');
require \realpath(__DIR__ . '/Source/DataType/Str.php');
require \realpath(__DIR__ . '/Source/Exception/Handler.php');
require \realpath(__DIR__ . '/Source/FileManager/Resolver.php');
require \realpath(__DIR__ . '/Source/FileManager/File.php');
require \realpath(__DIR__ . '/Source/FileManager/Symlink.php');
require \realpath(__DIR__ . '/Source/FileManager/Directory.php');
require \realpath(__DIR__ . '/Source/FileManager/FileType/Json.php');
require \realpath(__DIR__ . '/Source/PhpFile.php');
require \realpath(__DIR__ . '/Source/Git/GitHub.php');
