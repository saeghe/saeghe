<?php

spl_autoload_register(function ($class) {
    $class_map = [
        'Saeghe\Datatype\Collection' => realpath(__DIR__ . '/Packages/saeghe/datatype/Source/Collection.php'),
        'Saeghe\Datatype\Map' => realpath(__DIR__ . '/Packages/saeghe/datatype/Source/Map.php'),
        'Saeghe\Datatype\Pair' => realpath(__DIR__ . '/Packages/saeghe/datatype/Source/Pair.php'),
        'Saeghe\Datatype\Text' => realpath(__DIR__ . '/Packages/saeghe/datatype/Source/Text.php'),
        'Saeghe\Datatype\Tree' => realpath(__DIR__ . '/Packages/saeghe/datatype/Source/Tree.php'),
        'Saeghe\FileManager\Path' => realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Path.php'),
        'Saeghe\FileManager\Filesystem\Address' => realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Filesystem/Address.php'),
        'Saeghe\FileManager\Filesystem\Directory' => realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Filesystem/Directory.php'),
        'Saeghe\FileManager\Filesystem\File' => realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Filesystem/File.php'),
        'Saeghe\FileManager\Filesystem\Filename' => realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Filesystem/Filename.php'),
        'Saeghe\FileManager\Filesystem\FilesystemCollection' => realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Filesystem/FilesystemCollection.php'),
        'Saeghe\FileManager\Filesystem\FilesystemTree' => realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Filesystem/FilesystemTree.php'),
        'Saeghe\FileManager\Filesystem\Symlink' => realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Filesystem/Symlink.php'),
        'Saeghe\Saeghe\Classes\Build\Build' => realpath(__DIR__ . '/Source/Classes/Build/Build.php'),
        'Saeghe\Saeghe\Classes\Config\Config' => realpath(__DIR__ . '/Source/Classes/Config/Config.php'),
        'Saeghe\Saeghe\Classes\Config\Library' => realpath(__DIR__ . '/Source/Classes/Config/Library.php'),
        'Saeghe\Saeghe\Classes\Config\LinkPair' => realpath(__DIR__ . '/Source/Classes/Config/LinkPair.php'),
        'Saeghe\Saeghe\Classes\Config\NamespaceFilePair' => realpath(__DIR__ . '/Source/Classes/Config/NamespaceFilePair.php'),
        'Saeghe\Saeghe\Classes\Config\NamespacePathPair' => realpath(__DIR__ . '/Source/Classes/Config/NamespacePathPair.php'),
        'Saeghe\Saeghe\Classes\Config\PackageAlias' => realpath(__DIR__ . '/Source/Classes/Config/PackageAlias.php'),
        'Saeghe\Saeghe\Classes\Credential\Credential' => realpath(__DIR__ . '/Source/Classes/Credential/Credential.php'),
        'Saeghe\Saeghe\Classes\Credential\Credentials' => realpath(__DIR__ . '/Source/Classes/Credential/Credentials.php'),
        'Saeghe\Saeghe\Classes\Environment\Environment' => realpath(__DIR__ . '/Source/Classes/Environment/Environment.php'),
        'Saeghe\Saeghe\Classes\Meta\Dependency' => realpath(__DIR__ . '/Source/Classes/Meta/Dependency.php'),
        'Saeghe\Saeghe\Classes\Meta\Meta' => realpath(__DIR__ . '/Source/Classes/Meta/Meta.php'),
        'Saeghe\Saeghe\Classes\Package\Package' => realpath(__DIR__ . '/Source/Classes/Package/Package.php'),
        'Saeghe\Saeghe\Classes\Project\Project' => realpath(__DIR__ . '/Source/Classes/Project/Project.php'),
        'Saeghe\Saeghe\Exception\CredentialCanNotBeSetException' => realpath(__DIR__ . '/Source/Exception/CredentialCanNotBeSetException.php'),
        'Saeghe\Saeghe\Git\Repository' => realpath(__DIR__ . '/Source/Git/Repository.php'),
        'Saeghe\Saeghe\Git\Exception\InvalidTokenException' => realpath(__DIR__ . '/Source/Git/Exception/InvalidTokenException.php'),
        'Saeghe\Saeghe\PhpFile' => realpath(__DIR__ . '/Source/PhpFile.php'),
    ];

    require_once $class_map[$class];
});

require realpath(__DIR__ . '/Packages/saeghe/cli/Source/IO/Read.php');
require realpath(__DIR__ . '/Packages/saeghe/cli/Source/IO/Write.php');
require realpath(__DIR__ . '/Packages/saeghe/datatype/Source/Arr.php');
require realpath(__DIR__ . '/Packages/saeghe/datatype/Source/Str.php');
require realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Resolver.php');
require realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/File.php');
require realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Symlink.php');
require realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/Directory.php');
require realpath(__DIR__ . '/Packages/saeghe/file-manager/Source/FileType/Json.php');
require realpath(__DIR__ . '/Source/Exception/Handler.php');
require realpath(__DIR__ . '/Source/Git/GitHub.php');
