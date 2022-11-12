<?php

namespace Tests\PhpFileTest\UsedTraitsTest;

use Saeghe\Saeghe\PhpFile;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should return empty array when there is no trait',
    case: function () {
        $content = <<<EOD
<?php

class MyClass
{

}

EOD;

        $php_file = new PhpFile($content);

        assert_true([] === $php_file->used_traits());
    }
);

test(
    title: 'it should detect used trait',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

class MyClass
{
    use MyTrait;
}

EOD;

        $php_file = new PhpFile($content);

        assert_true(['MyApp\MyTrait'] === $php_file->used_traits());
    }
);

test(
    title: 'it should detect used traits',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use Application\OtherNamespace\MyOtherTrait;

class MyClass
{
    use MyTrait;
    use MyOtherTrait;
}

EOD;

        $php_file = new PhpFile($content);

        assert_true(['MyApp\MyTrait', 'Application\OtherNamespace\MyOtherTrait'] === $php_file->used_traits());
    }
);

test(
    title: 'it should detect nested used all traits',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use Application\OtherNamespace\MyOtherTrait;
use Application\Traits\{ATrait as FollowingTrait, HelloWorld }
use Application\CompoundNamespace as Concerns;

class MyClass
{
    use MyTrait;
    use MyOtherTrait, FollowingTrait;
    use HelloWorld { sayHello as protected; }
    use Concerns\ATrait;
    use \BuiltInTrait;
}

EOD;

        $php_file = new PhpFile($content);

        assert_true([
            'MyApp\MyTrait',
            'Application\OtherNamespace\MyOtherTrait',
            'Application\Traits\ATrait',
            'Application\Traits\HelloWorld',
            'Application\CompoundNamespace\ATrait',
            '\BuiltInTrait',
            ] === $php_file->used_traits()
        );
    }
);
