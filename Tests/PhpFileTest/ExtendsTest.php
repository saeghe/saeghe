<?php

namespace Tests\PhpFileTest\ExtendsTest;

require_once __DIR__ . '/../../Source/Str.php';
require_once __DIR__ . '/../../Source/PhpFile.php';

use Saeghe\Saeghe\PhpFile;

test(
    title: 'it should return null when there is no extend class',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

class MyClass
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert([] === $phpFile->extendedClasses());
    }
);

test(
    title: 'it should detect extend class for class',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

class MyClass extends ParentClass
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert(['MyApp\ParentClass'] === $phpFile->extendedClasses());
    }
);

test(
    title: 'it should detect extend class from php classes',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use \ArrayObject;

class MyClass extends ArrayObject
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert(['\ArrayObject'] === $phpFile->extendedClasses());
    }
);

test(
    title: 'it should detect extend class for class',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

class MyClass extends \ArrayObject
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert(['\ArrayObject'] === $phpFile->extendedClasses());
    }
);

test(
    title: 'it should detect extend class from imported classes',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use Application\ParentClass;

class MyClass extends ParentClass
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert(['Application\ParentClass'] === $phpFile->extendedClasses());
    }
);

test(
    title: 'it should detect extend class from compound namespaces',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use Application\SubNamespace as OtherNamespace;

class MyClass extends OtherNamespace\ParentClass
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert(['Application\SubNamespace\ParentClass'] === $phpFile->extendedClasses());
    }
);

test(
    title: 'it should detect extend class from alias',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use Application\SubNamespace\ParentClass as NormalClass;

class MyClass extends NormalClass
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert(['Application\SubNamespace\ParentClass'] === $phpFile->extendedClasses());
    }
);

test(
    title: 'it should detect extend class from aliases',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use Application\SubNamespace\{ParentClass as NormalClass, OtherClass };

class MyClass extends NormalClass
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert(['Application\SubNamespace\ParentClass'] === $phpFile->extendedClasses());
    }
);

test(
    title: 'it should detect extend class for nested extend class',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

class MyClass 
    extends ParentClass
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert(['MyApp\ParentClass'] === $phpFile->extendedClasses());
    }
);

test(
    title: 'it should detect extend with mixed interfaces and ugly code',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

class MyClass extends ParentClass implements AnInterface, OtherInterface
{

}
EOD;

        $phpFile = new PhpFile($content);

        assert(['MyApp\ParentClass'] === $phpFile->extendedClasses());
    }
);

test(
    title: 'it should detect extend class for abstract class',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

abstract class MyAbstractClass extends ParentAbstractClass

EOD;

        $phpFile = new PhpFile($content);

        assert(['MyApp\ParentAbstractClass'] === $phpFile->extendedClasses());
    }
);

test(
    title: 'it should detect extend class for interface class',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

interface MyInterface extends OtherInterface
{

}
EOD;

        $phpFile = new PhpFile($content);

        assert(['MyApp\OtherInterface'] === $phpFile->extendedClasses());
    }
);

test(
    title: 'it should detect extend class for trait',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

trait MyTrait extends OtherTrait {

}

EOD;

        $phpFile = new PhpFile($content);

        assert(['MyApp\OtherTrait'] === $phpFile->extendedClasses());
    }
);

test(
    title: 'it should detect extend interfaces for interface',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

interface FirstInterface extends SecondInterface,ThirdInterface, ForthInterface 
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert(['MyApp\SecondInterface', 'MyApp\ThirdInterface', 'MyApp\ForthInterface'] === $phpFile->extendedClasses());
    }
);
