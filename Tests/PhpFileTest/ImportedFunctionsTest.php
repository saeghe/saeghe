<?php

namespace Tests\PhpFileTest\ImportedFunctionsTest;

use Saeghe\Saeghe\PhpFile;
use function Saeghe\TestRunner\Assertions\Boolean\assert_true;

test(
    title: 'it should detect simple functions',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use function MyApp\OtherNamespace\\functionA;

EOD;

        $php_file = new PhpFile($content);

        assert_true([
            'MyApp\OtherNamespace\functionA' => 'functionA'
        ] === $php_file->imported_functions());
    }
);

test(
    title: 'it should detect multiple functions',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use function MyApp\OtherNamespace\{functionA, functionB};

EOD;

        $php_file = new PhpFile($content);

        assert_true([
                'MyApp\OtherNamespace\functionA' => 'functionA',
                'MyApp\OtherNamespace\functionB' => 'functionB',
            ] === $php_file->imported_functions()
        );
    }
);

test(
    title: 'it should detect functions when there is an alias',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use function MyApp\OtherNamespace\\functionA;
use function MyApp\OtherNamespace\{functionB, functionC as C, functionD};
use function MyApp\OtherNamespace\\functionE as functionF;

EOD;

        $php_file = new PhpFile($content);

        assert_true([
                'MyApp\OtherNamespace\functionA' => 'functionA',
                'MyApp\OtherNamespace\functionB' => 'functionB',
                'MyApp\OtherNamespace\functionC' => 'C',
                'MyApp\OtherNamespace\functionD' => 'functionD',
                'MyApp\OtherNamespace\functionE' => 'functionF',
            ] === $php_file->imported_functions()
        );
    }
);

test(
    title: 'it should detect functions when there more than one use in a line',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use MyApp\SomeClass;use function MyApp\OtherNamespace\\functionA;
use function MyApp\OtherNamespace\{functionB, functionC as C, functionD};use const MyApp\\ConstantA;
use function MyApp\OtherNamespace\\functionE as functionF;use function MyApp\OtherNamespace\\functionG;
use function MyApp\\fistFunction, MyApp\AnotherNamespace\secondFunction;

EOD;

        $php_file = new PhpFile($content);

        assert_true([
                'MyApp\OtherNamespace\functionA' => 'functionA',
                'MyApp\OtherNamespace\functionB' => 'functionB',
                'MyApp\OtherNamespace\functionC' => 'C',
                'MyApp\OtherNamespace\functionD' => 'functionD',
                'MyApp\OtherNamespace\functionE' => 'functionF',
                'MyApp\OtherNamespace\functionG' => 'functionG',
                'MyApp\\fistFunction' => 'fistFunction',
                'MyApp\AnotherNamespace\secondFunction' => 'secondFunction',
            ] === $php_file->imported_functions()
        );
    }
);
