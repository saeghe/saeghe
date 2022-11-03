<?php

require __DIR__ . DIRECTORY_SEPARATOR . 'Source' . DIRECTORY_SEPARATOR . 'Str.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'Source' . DIRECTORY_SEPARATOR . 'FileManager' . DIRECTORY_SEPARATOR . 'Path.php';
require __DIR__ . DIRECTORY_SEPARATOR . 'Source' . DIRECTORY_SEPARATOR . 'FileManager' . DIRECTORY_SEPARATOR . 'Address.php';

use Saeghe\Saeghe\FileManager\Address;

spl_autoload_register(function ($class) {
    $classMap = [
        'Saeghe\Saeghe\Git\Repository' => Address::from_string(__DIR__ . '/Source/Git/Repository.php')->to_string(),
        'Saeghe\Saeghe\Git\Exception\InvalidTokenException' => Address::from_string(__DIR__ . '/Source/Git/Exception/InvalidTokenException.php')->to_string(),
        'Saeghe\Saeghe\Config' => Address::from_string(__DIR__ . '/Source/Config.php')->to_string(),
        'Saeghe\Saeghe\Meta' => Address::from_string(__DIR__ . '/Source/Meta.php')->to_string(),
        'Saeghe\Saeghe\Package' => Address::from_string(__DIR__ . '/Source/Package.php')->to_string(),
        'Saeghe\Saeghe\Project' => Address::from_string(__DIR__ . '/Source/Project.php')->to_string(),
    ];

    require_once $classMap[$class];
});

require Address::from_string(__DIR__ . '/Packages/saeghe/cli/Source/IO/Read.php')->to_string();
require Address::from_string(__DIR__ . '/Packages/saeghe/cli/Source/IO/Write.php')->to_string();
require Address::from_string(__DIR__ . '/Source/PhpFile.php')->to_string();
require Address::from_string(__DIR__ . '/Source/FileManager/File.php')->to_string();
require Address::from_string(__DIR__ . '/Source/FileManager/Directory.php')->to_string();
require Address::from_string(__DIR__ . '/Source/FileManager/FileType/Json.php')->to_string();
require Address::from_string(__DIR__ . '/Source/Git/GitHub.php')->to_string();
require Address::from_string(__DIR__ . '/Source/Commands/Add.php')->to_string();
require Address::from_string(__DIR__ . '/Source/Commands/Build.php')->to_string();
require Address::from_string(__DIR__ . '/Source/Commands/Credential.php')->to_string();
require Address::from_string(__DIR__ . '/Source/Commands/Flush.php')->to_string();
require Address::from_string(__DIR__ . '/Source/Commands/Help.php')->to_string();
require Address::from_string(__DIR__ . '/Source/Commands/Init.php')->to_string();
require Address::from_string(__DIR__ . '/Source/Commands/Install.php')->to_string();
require Address::from_string(__DIR__ . '/Source/Commands/Man.php')->to_string();
require Address::from_string(__DIR__ . '/Source/Commands/Migrate.php')->to_string();
require Address::from_string(__DIR__ . '/Source/Commands/Remove.php')->to_string();
require Address::from_string(__DIR__ . '/Source/Commands/Update.php')->to_string();
require Address::from_string(__DIR__ . '/Source/Commands/Version.php')->to_string();
require Address::from_string(__DIR__ . '/Source/Commands/Watch.php')->to_string();
