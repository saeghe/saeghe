<?php

spl_autoload_register(function ($class) {
    $classMap = [
        'Saeghe\Exception\CredentialCanNotBeSetException' => \realpath(__DIR__ . '/Source/Exception/CredentialCanNotBeSetException.php'),
        'Saeghe\Saeghe\Git\Repository' => \realpath(__DIR__ . '/Source/Git/Repository.php'),
        'Saeghe\Saeghe\Git\Exception\InvalidTokenException' => \realpath(__DIR__ . '/Source/Git/Exception/InvalidTokenException.php'),
        'Saeghe\Saeghe\Config' => \realpath(__DIR__ . '/Source/Config.php'),
        'Saeghe\Saeghe\FileManager\DirectoryAddress' => \realpath(__DIR__ . '/Source/FileManager/DirectoryAddress.php'),
        'Saeghe\Saeghe\FileManager\FileAddress' => \realpath(__DIR__ . '/Source/FileManager/FileAddress.php'),
        'Saeghe\Saeghe\FileManager\SymlinkAddress' => \realpath(__DIR__ . '/Source/FileManager/SymlinkAddress.php'),
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
require \realpath(__DIR__ . '/Source/FileManager/Path.php');
require \realpath(__DIR__ . '/Source/FileManager/Address.php');
require \realpath(__DIR__ . '/Source/FileManager/File.php');
require \realpath(__DIR__ . '/Source/FileManager/Symlink.php');
require \realpath(__DIR__ . '/Source/FileManager/Directory.php');
require \realpath(__DIR__ . '/Source/FileManager/FileType/Json.php');
require \realpath(__DIR__ . '/Source/PhpFile.php');
require \realpath(__DIR__ . '/Source/Git/GitHub.php');
