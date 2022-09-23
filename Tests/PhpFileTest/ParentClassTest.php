<?php

namespace Tests\PhpFileTest\ParentClassTest;

require_once __DIR__ . '/../../Source/Str.php';
require_once __DIR__ . '/../../Source/PhpFile.php';

use Saeghe\Saeghe\PhpFile;

test(
    title: 'it should return null when there is no parent class',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

class MyClass
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert(null === $phpFile->parentClass());
    }
);

test(
    title: 'it should detect parent class for class',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

class MyClass extends ParentClass
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert('ParentClass' === $phpFile->parentClass());
    }
);

test(
    title: 'it should detect parent class for nested parent class',
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

        assert('ParentClass' === $phpFile->parentClass());
    }
);

test(
    title: 'it should detect parent with mixed interfaces and ugly code',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

class MyClass extends ParentClass implements AnInterface, OtherInterface
{

}
EOD;

        $phpFile = new PhpFile($content);

        assert('ParentClass' === $phpFile->parentClass());
    }
);

test(
    title: 'it should detect parent class for abstract class',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

abstract class MyAbstractClass extends ParentAbstractClass

EOD;

        $phpFile = new PhpFile($content);

        assert('ParentAbstractClass' === $phpFile->parentClass());
    }
);

test(
    title: 'it should detect parent class for interface class',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

interface MyInterface extends OtherInterface
{

}
EOD;

        $phpFile = new PhpFile($content);

        assert('OtherInterface' === $phpFile->parentClass());
    }
);

test(
    title: 'it should detect parent class for trait',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

trait MyTrait extends OtherTrait {

}

EOD;

        $phpFile = new PhpFile($content);

        assert('OtherTrait' === $phpFile->parentClass());
    }
);
