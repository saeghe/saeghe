<?php

namespace Tests\PhpFileTest\IsOopTest;

require_once __DIR__ . '/../../Source/PhpFile.php';

use Saeghe\Saeghe\PhpFile;

test(
    title: 'it should return false for simple php file',
    case: function () {
        $content = <<<'EOD'
<?php

namespace Application;

call_function();

EOD;
        assert(! (new PhpFile($content))->isOop());
    }
);

test(
    title: 'it should return true for classes',
    case: function () {
        $content = <<<'EOD'
<?php

namespace Application;

class MyClass
{

}

EOD;
        assert((new PhpFile($content))->isOop());
    }
);

test(
    title: 'it should return true for abstract classes',
    case: function () {
        $content = <<<'EOD'
<?php

namespace Application;

abstract class MyClass
{

}

EOD;
        assert((new PhpFile($content))->isOop());
    }
);

test(
    title: 'it should return true for interfaces',
    case: function () {
        $content = <<<'EOD'
<?php

namespace Application;

interface MyClass
{

}

EOD;
        assert((new PhpFile($content))->isOop());
    }
);

test(
    title: 'it should return true for traits',
    case: function () {
        $content = <<<'EOD'
<?php

namespace Application;

trait MyClass
{

}

EOD;
        assert((new PhpFile($content))->isOop());
    }
);

test(
    title: 'it should return true for enums',
    case: function () {
        $content = <<<'EOD'
<?php

namespace Application;

enum MyClass
{

}

EOD;
        assert((new PhpFile($content))->isOop());
    }
);
