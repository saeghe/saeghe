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

        assert(['MyApp\MyInterface'] === $phpFile->implementedInterfaces());
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

        assert(['MyApp\FirstInterface', 'MyApp\SecondInterface', 'MyApp\ThirdInterface'] === $phpFile->implementedInterfaces());
    }
);

test(
    title: 'it should detect implemented interfaces with compound namespaces',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use Application\FirstInterface;
use Application\ForthInterface as SecondInterface;
use Application\Namespaces\{ThirdInterface, FifthInterface as SixthInterface};
use Application\CompoundNamespace as OtherNamespace
use \ArrayAccess

class MyClass extends ParentClass implements FirstInterface, SecondInterface, ThirdInterface , SixthInterface, OtherNamespace\AnyNamespace, \Iterator, ArrayAccess extends JustForTest
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert([
                'Application\FirstInterface',
                'Application\ForthInterface',
                'Application\Namespaces\ThirdInterface',
                'Application\Namespaces\FifthInterface',
                'Application\CompoundNamespace\AnyNamespace',
                '\Iterator',
                '\ArrayAccess',
            ] === $phpFile->implementedInterfaces()
        );
    }
);
