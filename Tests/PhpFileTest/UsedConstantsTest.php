<?php

namespace Tests\UsedConstantsTest;

use Saeghe\Saeghe\PhpFile;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should return used constants',
    case: function () {
        $content = <<<'EOD'
<?php

namespace Application;

use Application\AnyNamespace\UsedClass;
use Application\AnyNamespace\UsedClassStatic;
use const Application\Constants\CONST_E;
use const Application\Constants\CONST_F as F;
use const Application\Constants\{CONST_G as G, CONST_H, CONST_NOT_USED as NOT_USED};
use Model;
use Application\SubNamespace as OtherNamespace;
use Application\OtherNamespace\{ClassA as A, ClassB};
use const PHP_BINARY;
use const \PHP_BINARY_READ;

class MyClass
{
    public function __construct() {
        self::CONST_A;
        $var = static::CONST_B;
        $varParent = parent::CONST_PARENT;
    }
    
    public function method()
    {
        NotConst::class;
        ClassInSameNamespace::CONST_C;
        UsedClass::CONST_D;
        $varE = CONST_E;
        $varF = F;
        $varG = G;
        $varH = CONST_H;
        UsedClassStatic::call();
        Model::CONST_1;
        OtherNamespace\SubClass::CONST_A;
        A::CONST_I;
        ClassB::CONST_J;
        \ReflectionProperty::IS_PUBLIC
        $class::CONST;
        \PHP_EOL;
        PHP_BINARY;
        \PHP_BINARY_READ;
    }
}
EOD;

        assert_true(
            [
                'Application\ClassInSameNamespace\CONST_C',
                'Application\AnyNamespace\UsedClass\CONST_D',
                'Model\CONST_1',
                'Application\SubNamespace\SubClass\CONST_A',
                'Application\OtherNamespace\ClassA\CONST_I',
                'Application\OtherNamespace\ClassB\CONST_J',
                '\ReflectionProperty\IS_PUBLIC',
                'Application\Constants\CONST_E',
                'Application\Constants\CONST_F',
                'Application\Constants\CONST_G',
                'Application\Constants\CONST_H',
                'PHP_BINARY',
                '\PHP_BINARY_READ',
            ] === (new PhpFile($content))->used_constants(),
            'Class constants are not detected!'
        );
    }
);
