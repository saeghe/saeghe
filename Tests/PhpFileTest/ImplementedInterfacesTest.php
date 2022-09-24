<?php

namespace Tests\PhpFileTest\ImplementedInterfacesTest;

require_once __DIR__ . '/../../Source/Str.php';
require_once __DIR__ . '/../../Source/PhpFile.php';

use Saeghe\Saeghe\PhpFile;

test(
    title: 'it should return empty array when there is no namespace',
    case: function () {
        $content = <<<EOD
<?php

class MyClass
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert([] === $phpFile->implementedInterfaces());
    }
);

test(
    title: 'it should detect implemented interface',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

class MyClass implements MyInterface
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert(['MyInterface'] === $phpFile->implementedInterfaces());
    }
);

test(
    title: 'it should detect implemented interfaces',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

class MyClass extends ParentClass implements FirstInterface, SecondInterface, ThirdInterface extends JustForTest
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert(['FirstInterface', 'SecondInterface', 'ThirdInterface'] === $phpFile->implementedInterfaces());
    }
);
