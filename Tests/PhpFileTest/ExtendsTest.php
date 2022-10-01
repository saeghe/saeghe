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

        assert(['ParentClass'] === $phpFile->extendedClasses());
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

        assert(['ParentClass'] === $phpFile->extendedClasses());
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

        assert(['ParentClass'] === $phpFile->extendedClasses());
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

        assert(['ParentAbstractClass'] === $phpFile->extendedClasses());
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

        assert(['OtherInterface'] === $phpFile->extendedClasses());
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

        assert(['OtherTrait'] === $phpFile->extendedClasses());
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

        assert(['SecondInterface', 'ThirdInterface', 'ForthInterface'] === $phpFile->extendedClasses());
    }
);
