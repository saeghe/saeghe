<?php

namespace Tests\PhpFileTest\UsedTraitsTest;

require_once __DIR__ . '/../../Source/Str.php';
require_once __DIR__ . '/../../Source/PhpFile.php';

use Saeghe\Saeghe\PhpFile;

test(
    title: 'it should return empty array when there is no trait',
    case: function () {
        $content = <<<EOD
<?php

class MyClass
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert([] === $phpFile->usedTraits());
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

        $phpFile = new PhpFile($content);

        assert(['MyApp\MyTrait'] === $phpFile->usedTraits());
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

        $phpFile = new PhpFile($content);

        assert(['MyApp\MyTrait', 'Application\OtherNamespace\MyOtherTrait'] === $phpFile->usedTraits());
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
}

EOD;

        $phpFile = new PhpFile($content);

        assert([
            'MyApp\MyTrait',
            'Application\OtherNamespace\MyOtherTrait',
            'Application\Traits\ATrait',
            'Application\Traits\HelloWorld',
            'Application\CompoundNamespace\ATrait',
            ] === $phpFile->usedTraits()
        );
    }
);
