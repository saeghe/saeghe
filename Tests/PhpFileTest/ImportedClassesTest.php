<?php

namespace Tests\PhpFileTest\ImportedClassesTest;

use Saeghe\Saeghe\PhpFile;

test(
    title: 'it should detect simple classes',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use MyApp\OtherNamespace\MyClass;

EOD;

        $php_file = new PhpFile($content);

        assert([
            'MyApp\OtherNamespace\MyClass' => 'MyClass'
        ] === $php_file->imported_classes());
    }
);

test(
    title: 'it should detect simple classes contain traits',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use MyApp\OtherNamespace\ClassA;

class MyClass extends ParentClass implements MyInterface
{
    use MyTrait;
}

EOD;

        $php_file = new PhpFile($content);

        assert(['MyApp\OtherNamespace\ClassA' => 'ClassA'] === $php_file->imported_classes());
    }
);

test(
    title: 'it should detect simple classes contain traits in final classes',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use MyApp\OtherNamespace\ClassA;

final class MyClass extends ParentClass implements MyInterface
{
    use MyTrait;
}

EOD;

        $php_file = new PhpFile($content);

        assert(['MyApp\OtherNamespace\ClassA' => 'ClassA'] === $php_file->imported_classes());
    }
);

test(
    title: 'it should detect multiple classes',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use MyApp\OtherNamespace\{ClassA, ClassB};

EOD;

        $php_file = new PhpFile($content);

        assert([
                'MyApp\OtherNamespace\ClassA' => 'ClassA',
                'MyApp\OtherNamespace\ClassB' => 'ClassB',
            ] === $php_file->imported_classes()
        );
    }
);

test(
    title: 'it should detect classes when there is an alias',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use MyApp\OtherNamespace\ClassA;
use MyApp\OtherNamespace\{ClassB, ClassC as C, ClassD};
use MyApp\OtherNamespace\ClassE as ClassF;

EOD;

        $php_file = new PhpFile($content);

        assert([
                'MyApp\OtherNamespace\ClassA' => 'ClassA',
                'MyApp\OtherNamespace\ClassB' => 'ClassB',
                'MyApp\OtherNamespace\ClassC' => 'C',
                'MyApp\OtherNamespace\ClassD' => 'ClassD',
                'MyApp\OtherNamespace\ClassE' => 'ClassF',
            ] === $php_file->imported_classes()
        );
    }
);

test(
    title: 'it should detect classes when there more than one use in a line',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use function MyApp\\functionA;use MyApp\OtherNamespace\ClassA;
use MyApp\OtherNamespace\{ClassB, ClassC as C, ClassD};use const MyApp\ConstantA;
use MyApp\OtherNamespace\ClassE as ClassF;use MyApp\OtherNamespace\ClassG;
use MyApp\FirstClass, MyApp\AnotherNamespace\SecondClass;

EOD;

        $php_file = new PhpFile($content);

        assert([
                'MyApp\AnotherNamespace\SecondClass' => 'SecondClass',
                'MyApp\OtherNamespace\ClassA' => 'ClassA',
                'MyApp\OtherNamespace\ClassB' => 'ClassB',
                'MyApp\OtherNamespace\ClassC' => 'C',
                'MyApp\OtherNamespace\ClassD' => 'ClassD',
                'MyApp\OtherNamespace\ClassE' => 'ClassF',
                'MyApp\OtherNamespace\ClassG' => 'ClassG',
                'MyApp\FirstClass' => 'FirstClass',
            ] === $php_file->imported_classes()
        );
    }
);
