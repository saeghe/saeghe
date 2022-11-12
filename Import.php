<?php

spl_autoload_register(function ($class) {
    $classMap = [
        'Saeghe\Saeghe\Exception\CredentialCanNotBeSetException' => \realpath(__DIR__ . '/Source/Exception/CredentialCanNotBeSetException.php'),
        'Saeghe\Saeghe\Git\Repository' => \realpath(__DIR__ . '/Source/Git/Repository.php'),
        'Saeghe\Saeghe\Git\Exception\InvalidTokenException' => \realpath(__DIR__ . '/Source/Git/Exception/InvalidTokenException.php'),
        'Saeghe\Saeghe\Config\Config' => \realpath(__DIR__ . '/Source/Config/Config.php'),
        'Saeghe\Saeghe\Config\EntryPoints' => \realpath(__DIR__ . '/Source/Config/EntryPoints.php'),
        'Saeghe\Saeghe\Config\Excludes' => \realpath(__DIR__ . '/Source/Config/Excludes.php'),
        'Saeghe\Saeghe\Config\Executables' => \realpath(__DIR__ . '/Source/Config/Executables.php'),
        'Saeghe\Saeghe\Config\Map' => \realpath(__DIR__ . '/Source/Config/Map.php'),
        'Saeghe\Saeghe\Config\Meta' => \realpath(__DIR__ . '/Source/Config/Meta.php'),
        'Saeghe\Saeghe\Config\Packages' => \realpath(__DIR__ . '/Source/Config/Packages.php'),
        'Saeghe\Saeghe\Datatype\ArrayCollection' => \realpath(__DIR__ . '/Source/Datatype/ArrayCollection.php'),
        'Saeghe\Saeghe\Datatype\Collection' => \realpath(__DIR__ . '/Source/Datatype/Collection.php'),
        'Saeghe\Saeghe\Datatype\AnyText' => \realpath(__DIR__ . '/Source/Datatype/AnyText.php'),
        'Saeghe\Saeghe\Datatype\Text' => \realpath(__DIR__ . '/Source/Datatype/Text.php'),
        'Saeghe\Saeghe\FileManager\Path' => \realpath(__DIR__ . '/Source/FileManager/Path.php'),
        'Saeghe\Saeghe\FileManager\Filesystem\Address' => \realpath(__DIR__ . '/Source/FileManager/Filesystem/Address.php'),
        'Saeghe\Saeghe\FileManager\Filesystem\Directory' => \realpath(__DIR__ . '/Source/FileManager/Filesystem/Directory.php'),
        'Saeghe\Saeghe\FileManager\Filesystem\File' => \realpath(__DIR__ . '/Source/FileManager/Filesystem/File.php'),
        'Saeghe\Saeghe\FileManager\Filesystem\Filename' => \realpath(__DIR__ . '/Source/FileManager/Filesystem/Filename.php'),
        'Saeghe\Saeghe\FileManager\Filesystem\FilesystemCollection' => \realpath(__DIR__ . '/Source/FileManager/Filesystem/FilesystemCollection.php'),
        'Saeghe\Saeghe\FileManager\Filesystem\Symlink' => \realpath(__DIR__ . '/Source/FileManager/Filesystem/Symlink.php'),
        'Saeghe\Saeghe\Package' => \realpath(__DIR__ . '/Source/Package.php'),
        'Saeghe\Saeghe\Project' => \realpath(__DIR__ . '/Source/Project.php'),
    ];

    require_once $classMap[$class];
});

require \realpath(__DIR__ . '/Packages/saeghe/cli/Source/IO/Read.php');
require \realpath(__DIR__ . '/Packages/saeghe/cli/Source/IO/Write.php');
require \realpath(__DIR__ . '/Source/Datatype/Arr.php');
require \realpath(__DIR__ . '/Source/Datatype/Str.php');
require \realpath(__DIR__ . '/Source/Exception/Handler.php');
require \realpath(__DIR__ . '/Source/FileManager/Resolver.php');
require \realpath(__DIR__ . '/Source/FileManager/File.php');
require \realpath(__DIR__ . '/Source/FileManager/Symlink.php');
require \realpath(__DIR__ . '/Source/FileManager/Directory.php');
require \realpath(__DIR__ . '/Source/FileManager/FileType/Json.php');
require \realpath(__DIR__ . '/Source/PhpFile.php');
require \realpath(__DIR__ . '/Source/Git/GitHub.php');
