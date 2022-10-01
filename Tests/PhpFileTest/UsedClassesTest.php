<?php

namespace Tests\PhpFileTest\UsedClassesTest;

require_once __DIR__ . '/../../Source/Str.php';
require_once __DIR__ . '/../../Source/PhpFile.php';

use Saeghe\Saeghe\PhpFile;

test(
    title: 'it should detect simple classes',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use MyApp\OtherNamespace\ClassA;

EOD;

        $phpFile = new PhpFile($content);

        assert(['MyApp\OtherNamespace\ClassA' => 'ClassA'] === $phpFile->usedClasses());
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

        $phpFile = new PhpFile($content);

        assert(['MyApp\OtherNamespace\ClassA' => 'ClassA'] === $phpFile->usedClasses());
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

        $phpFile = new PhpFile($content);

        assert([
                'MyApp\OtherNamespace\ClassA' => 'ClassA',
                'MyApp\OtherNamespace\ClassB' => 'ClassB',
            ] === $phpFile->usedClasses()
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

class MyClass
{
    public function __invoke()
    {
        new ClassB;
        new C(ClassF::call('something'))
    }
}
EOD;

        $phpFile = new PhpFile($content);

        assert([
                'MyApp\OtherNamespace\ClassA' => 'ClassA',
                'MyApp\OtherNamespace\ClassB' => 'ClassB',
                'MyApp\OtherNamespace\ClassC' => 'C',
                'MyApp\OtherNamespace\ClassD' => 'ClassD',
                'MyApp\OtherNamespace\ClassE' => 'ClassF',
            ] === $phpFile->usedClasses()
        );
    }
);

test(
    title: 'it should detect classes when there more than one use in a line',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use const MyApp\SomeClass;use MyApp\OtherNamespace\ClassA;
use MyApp\OtherNamespace\{ClassB, ClassC as C, ClassD};use function MyApp\\function;
use MyApp\OtherNamespace\ClassE as ClassF;use MyApp\OtherNamespace\ClassG;
use MyApp\FirstClass, MyApp\AnotherNamespace\SecondClass;

class MyClass
{
    public function __construct()
    {
        new C;
        ClassF::call();
    }
}
EOD;

        $phpFile = new PhpFile($content);

        assert([
                'MyApp\OtherNamespace\ClassA' => 'ClassA',
                'MyApp\OtherNamespace\ClassB' => 'ClassB',
                'MyApp\OtherNamespace\ClassC' => 'C',
                'MyApp\OtherNamespace\ClassD' => 'ClassD',
                'MyApp\OtherNamespace\ClassE' => 'ClassF',
                'MyApp\OtherNamespace\ClassG' => 'ClassG',
                'MyApp\FirstClass' => 'FirstClass',
                'MyApp\AnotherNamespace\SecondClass' => 'SecondClass',
            ] === $phpFile->usedClasses()
        );
    }
);

test(
    title: 'it should detect used classes with compounded namespace',
    case: function () {
        $content = <<<EOD
<?php

namespace ProjectWithTests\CompoundNamespace;

use ProjectWithTests\CompoundNamespace\Foo as CompoundFoo;

class UseCompoundNamespace
{
    public function run()
    {
        (new CompoundFoo\ClassFoo());
        CompoundFoo\ClassBar::class;
        CompoundFoo\ClassBaz::boot();
        CompoundFoo\InnerNamespace\ClassBaz::run();
    }
}

EOD;
        $phpFile = new PhpFile($content);

        assert([
                'ProjectWithTests\CompoundNamespace\Foo\ClassFoo' => 'ClassFoo',
                'ProjectWithTests\CompoundNamespace\Foo\ClassBaz' => 'ClassBaz',
                'ProjectWithTests\CompoundNamespace\Foo\InnerNamespace\ClassBaz' => 'ClassBaz',
            ] === $phpFile->usedClasses()
        );
    }
);

test(
    title: 'it should not return namespace aliases as used classes when there is no usages',
    case: function () {
        $content = <<<EOD
<?php

namespace ProjectWithTests\CompoundNamespace;

use ProjectWithTests\CompoundNamespace as Compound;
use ProjectWithTests\UsedStaticallyCompoundNamespace as UsedStaticallyCompound;
use ProjectWithTests\UsedNewCompoundNamespace as UsedNewCompound;
use ProjectWithTests\UnusedClass;

class UseCompoundNamespace
{
    public function run()
    {
        Compound\ClassBar::class;
        UsedStaticallyCompound\ClassFoo::call();
        new UsedNewCompound\ClassFoo();
    }
}

EOD;
        $phpFile = new PhpFile($content);

        assert([
                'ProjectWithTests\UsedStaticallyCompoundNamespace\ClassFoo' => 'ClassFoo',
                'ProjectWithTests\UsedNewCompoundNamespace\ClassFoo' => 'ClassFoo',
                'ProjectWithTests\UnusedClass' => 'UnusedClass',
            ] === $phpFile->usedClasses()
        );
    }
);

test(
    title: 'it should detect functions',
    case: function () {
        $content = <<<EOD
<?php

namespace ProjectWithTests\CompoundNamespace;

use ProjectWithTests\Functions as Helper;
use ProjectWithTests\Classes as HelperClass;
use ProjectWithTests\StaticClass as StaticHelper;

class UseCompoundNamespace
{
    public function run()
    {
        Helper\aFunction();
        new HelperClass();
        StaticHelper::call();
    }
}

EOD;
        $phpFile = new PhpFile($content);

        assert([
                'ProjectWithTests\Functions' => '',
                'ProjectWithTests\Classes' => 'HelperClass',
                'ProjectWithTests\StaticClass' => 'StaticHelper',
            ] === $phpFile->usedClasses()
        );
    }
);
