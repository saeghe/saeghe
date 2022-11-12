<?php

namespace Tests\UsedClassesTest;

use Saeghe\Saeghe\PhpFile;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should return used classes',
    case: function () {
        $content = <<<'EOD'
<?php

namespace Application;

use Application\ParentClass;
use Application\Interfaces\InterfaceA;
use Application\Interfaces\InterfaceB;
use Application\Traits\MyTrait;
use Application\Types\ConstructorInjectedClass;
use Application\Types\MethodInjectedClassA;
use Application\Types\MethodInjectedClassB;
use Application\Types\MethodReturnTypeA;
use Application\Types\MethodReturnTypeB;
use Application\Statics\ClassA;
use Application\Statics\CompoundNamespace as ClassC;
use Application\Statics\{ClassE, ClassF as G, UnusedClass, GetClassName};
use Application\Instances\InstanceA;
use Application\Instances\CompoundNamespace as InstanceC;
use Application\Instances\{InstanceE, InstanceF as InstanceG, UnusedInstance};
use User;
use Application\Attributes\SetUp;
use Application\Constants\ClassH;

/**
 * It should Ignore any call in comment
 * AnyCommentClass::static or new AnyInstanceClass()
 * Should get ignored 
 */
class MyClass extends ParentClass implements InterfaceA, interfaceB
{
    use MyTrait;
    
    public function __construct(public readonly ConstructorInjectedClass $constructorInjectedClass) 
    {
        parent::__constructor();
        self::sttic_call();
        static::__call();
    }
    
    public function method_typed_call(MethodInjectedClassA|MethodInjectedClassB $input)
    {
    
    }
    
    public function method_return_typed(): MethodReturnTypeA|MethodRreturnTypeB
    {
    
    }
    
    public static function calls()
    {
        // Inline comments should get ignore for static ClassInComment::call()
        $string = "%s::any should not get involve"
        ClassA::static_call();
        ClassC\ClassD::another_call();
        ClassE::call();
        GetClassName::class;
        if (G::call()) {
        
        }

        Application\SubDirectory\ClassUseAnotherStaticClass::call();
        \Locale::setDefault('en');
        StaticClassB::run(StaticClassC::output($staticCall))
    }
    
    public function instantiation()
    {
        // Inline comments should get ignore for new ClassInComment()
        $string = "new ClassInString() should not get involve";
        new self();
        new parent();
        new static();
        $var = new InstanceA;

        if ($var2 = (new InstanceC\InstanceD())) {
        
        }
        
        new InstanceE($paramteres);
        new InstanceG(new SameNamespaceClass());
        new User();
        new \ArrayObject();
        $newInstanceClassWithoutUse = new Application\SubDirectory\ClassUseAnotherClass();
    }
    
    public function constants()
    {
        ClassG::Const_A;
    }
    
    [#SetUp]
    public function useAttributes()
    {
    
    }
}
EOD;

        assert_true(
            [
                'Application\Statics\ClassA',
                'Application\Statics\CompoundNamespace\ClassD',
                'Application\Statics\ClassE',
                'Application\Statics\ClassF',
                'Application\SubDirectory\ClassUseAnotherStaticClass',
                'Application\StaticClassB',
                'Application\StaticClassC',
                'Application\Instances\InstanceA',
                'Application\Instances\CompoundNamespace\InstanceD',
                'Application\Instances\InstanceE',
                'Application\Instances\InstanceF',
                'Application\SameNamespaceClass',
                'User',
                'Application\SubDirectory\ClassUseAnotherClass'
            ] === (new PhpFile($content))->used_classes(),
            'Used classes are not detected!'
        );
    }
);
