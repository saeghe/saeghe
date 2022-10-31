<?php

namespace Tests\UsedFunctionsTest;

use Saeghe\Saeghe\PhpFile;

test(
    title: 'it should return used functions',
    case: function () {
        $content = <<<'EOD'
<?php

namespace Application;

use function Application\Helpers\function_a;
use function Application\Helpers\function_b as function_c;
use function Application\Helpers\{function_d, function_e as g, unused_function};
use Application\Application;
use Application\CompoundNamespace as OtherNamespace;
use Application\OtherHelper as AwesomeHelper;
use Application\Model\{User, Order as Orders, NotUsed};
use Application\CallFromNewInstance;
use Application\Str;
use Application\CompoundNamespaceClass as Compound;
use function \str_pad;

class MyClass
{
    public function __construct() {
        self::call();
        static::__construct();
        parent::a_method();
    }
    
    public function method()
    {
        str_replace($search, $replace, $subject);
        \strlen($var);
        SameNamespace\method_call($parameters);
        $varA = function_a();
        $varC = function_c();
        $varD = function_d();
        $varE = g();
        $dynamicCall();
        str_pad();
        \str_ends_with();
    }
    
    protected function my_method()
    {
        OtherNamespace\HelperFiles\methodCall();
        if (AwesomeHelper\function_a()) {
        
        }
    }
     
    private function otherMethod()
    {   
        User::login($user, $password);
        Orders::sendEmail('info@example.com');
        (new CallFromNewInstance)->callMethod($parameters);
        $this->methodNotCountedInFunctions();
        Str\remove_last_character($subject);
    }
    
    function newInstancesShouldNotBeIncluded()
    {
        new Application($parameters);
        new Compound();
    }
}
EOD;

        assert(
            [
                'str_replace',
                '\strlen',
                'Application\SameNamespace\method_call',
                '\str_ends_with',
                'Application\CompoundNamespace\HelperFiles\methodCall',
                'Application\OtherHelper\function_a',
                'Application\Str\remove_last_character',
                'Application\Helpers\function_a',
                'Application\Helpers\function_b',
                'Application\Helpers\function_d',
                'Application\Helpers\function_e',
                '\str_pad',
            ] === (new PhpFile($content))->used_functions(),
            'Used functions are not detected!'
        );
    }
);
