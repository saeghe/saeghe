<?php

namespace Tests\PhpFileTest\ExtendsTest;

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

        $php_file = new PhpFile($content);

        assert_true([] === $php_file->extended_classes());
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

        $php_file = new PhpFile($content);

        assert_true(['MyApp\ParentClass'] === $php_file->extended_classes());
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

        $php_file = new PhpFile($content);

        assert_true(['\ArrayObject'] === $php_file->extended_classes());
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

        $php_file = new PhpFile($content);

        assert_true(['\ArrayObject'] === $php_file->extended_classes());
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

        $php_file = new PhpFile($content);

        assert_true(['Application\ParentClass'] === $php_file->extended_classes());
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

        $php_file = new PhpFile($content);

        assert_true(['Application\SubNamespace\ParentClass'] === $php_file->extended_classes());
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

        $php_file = new PhpFile($content);

        assert_true(['Application\SubNamespace\ParentClass'] === $php_file->extended_classes());
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

        $php_file = new PhpFile($content);

        assert_true(['Application\SubNamespace\ParentClass'] === $php_file->extended_classes());
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

        $php_file = new PhpFile($content);

        assert_true(['MyApp\ParentClass'] === $php_file->extended_classes());
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

        $php_file = new PhpFile($content);

        assert_true(['MyApp\ParentClass'] === $php_file->extended_classes());
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

        $php_file = new PhpFile($content);

        assert_true(['MyApp\ParentAbstractClass'] === $php_file->extended_classes());
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

        $php_file = new PhpFile($content);

        assert_true(['MyApp\OtherInterface'] === $php_file->extended_classes());
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

        $php_file = new PhpFile($content);

        assert_true(['MyApp\OtherTrait'] === $php_file->extended_classes());
    }
);

test(
    title: 'it should detect extend interfaces for interface',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use \ArrayAccess;

interface FirstInterface extends SecondInterface,ThirdInterface, ForthInterface, \Iterator, ArrayAccess
{

}

EOD;

        $php_file = new PhpFile($content);

        assert_true([
            'MyApp\SecondInterface',
            'MyApp\ThirdInterface',
            'MyApp\ForthInterface',
            '\Iterator',
            '\ArrayAccess',
        ] === $php_file->extended_classes());
    }
);
