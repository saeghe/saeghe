<?php

namespace Tests\UsedFunctionsTest;

require_once __DIR__ . '/../../Source/PhpFile.php';

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
use Application\CompoundNamespace as OtherNamespace;
use Application\OtherHelper as AwesomeHelper;
use Application\Model\{User, Order as Orders, NotUsed};
use Application\CallFromNewInstance;
use Application\Str;

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
        SameNamespace\method_call($parameters);
        $varA = function_a();
        $varC = function_c();
        $varD = function_d();
        $varE = g();
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
}
EOD;

        assert(
            [
                'str_replace',
                'Application\SameNamespace\method_call',
                'Application\CompoundNamespace\HelperFiles\methodCall',
                'Application\OtherHelper\function_a',
                'Application\Str\remove_last_character',
                'Application\Helpers\function_a',
                'Application\Helpers\function_b',
                'Application\Helpers\function_d',
                'Application\Helpers\function_e',
            ] === (new PhpFile($content))->usedFunctions(),
            'Used functions are not detected!'
        );
    }
);
