<?php

if (PHP_VERSION_ID < 80100) {
    define('T_ENUM', 336);
}

spl_autoload_register(function ($class) {
    $classMap = [
        'Saeghe\Datatype\ArrayCollection' => \realpath(__DIR__ . '/Packages/saeghe/datatype/Source/ArrayCollection.php'),
        'Saeghe\Datatype\Collection' => \realpath(__DIR__ . '/Packages/saeghe/datatype/Source/Collection.php'),
        'Saeghe\Datatype\AnyText' => \realpath(__DIR__ . '/Packages/saeghe/datatype/Source/AnyText.php'),
        'Saeghe\Datatype\Text' => \realpath(__DIR__ . '/Packages/saeghe/datatype/Source/Text.php'),
        'Saeghe\FileManager\Path' => \realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Path.php'),
        'Saeghe\FileManager\Filesystem\Address' => \realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Filesystem/Address.php'),
        'Saeghe\FileManager\Filesystem\Directory' => \realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Filesystem/Directory.php'),
        'Saeghe\FileManager\Filesystem\File' => \realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Filesystem/File.php'),
        'Saeghe\FileManager\Filesystem\Filename' => \realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Filesystem/Filename.php'),
        'Saeghe\FileManager\Filesystem\FilesystemCollection' => \realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Filesystem/FilesystemCollection.php'),
        'Saeghe\FileManager\Filesystem\Symlink' => \realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Filesystem/Symlink.php'),
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
        'Saeghe\Saeghe\Package' => \realpath(__DIR__ . '/Source/Package.php'),
        'Saeghe\Saeghe\PhpFile' => \realpath(__DIR__ . '/Source/PhpFile.php'),
        'Saeghe\Saeghe\Project' => \realpath(__DIR__ . '/Source/Project.php'),
    ];

    require_once $classMap[$class];
});

require \realpath(__DIR__ . '/Packages/saeghe/cli/Source/IO/Read.php');
require \realpath(__DIR__ . '/Packages/saeghe/cli/Source/IO/Write.php');
require \realpath(__DIR__ . '/Packages/saeghe/datatype/Source/Arr.php');
require \realpath(__DIR__ . '/Packages/saeghe/datatype/Source/Str.php');
require \realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Resolver.php');
require \realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/File.php');
require \realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Symlink.php');
require \realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Directory.php');
require \realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/FileType/Json.php');
require \realpath(__DIR__ . '/Source/Exception/Handler.php');
require \realpath(__DIR__ . '/Source/Git/GitHub.php');
