<?php

namespace Tests\PhpFileTest\ImportedConstantsTest;

use Saeghe\Saeghe\PhpFile;

test(
    title: 'it should detect simple constants',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use const MyApp\OtherNamespace\ConstantA;

EOD;

        $php_file = new PhpFile($content);

        assert_true(['MyApp\OtherNamespace\ConstantA' => 'ConstantA'] === $php_file->imported_constants());
    }
);

test(
    title: 'it should detect multiple constants',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use const MyApp\OtherNamespace\{ConstantA, ConstantB};

EOD;

        $php_file = new PhpFile($content);

        assert_true([
                'MyApp\OtherNamespace\ConstantA' => 'ConstantA',
                'MyApp\OtherNamespace\ConstantB' => 'ConstantB',
            ] === $php_file->imported_constants()
        );
    }
);

test(
    title: 'it should detect constants when there is an alias',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use const MyApp\OtherNamespace\ConstantA;
use const MyApp\OtherNamespace\{ConstantB, ConstantC as C, ConstantD};
use const MyApp\OtherNamespace\ConstantE as ConstantF;

EOD;

        $php_file = new PhpFile($content);

        assert_true([
                'MyApp\OtherNamespace\ConstantA' => 'ConstantA',
                'MyApp\OtherNamespace\ConstantB' => 'ConstantB',
                'MyApp\OtherNamespace\ConstantC' => 'C',
                'MyApp\OtherNamespace\ConstantD' => 'ConstantD',
                'MyApp\OtherNamespace\ConstantE' => 'ConstantF',
            ] === $php_file->imported_constants()
        );
    }
);

test(
    title: 'it should detect constants when there more than one use in a line',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

use MyApp\SomeClass;use const MyApp\OtherNamespace\ConstantA;
use const MyApp\OtherNamespace\{ConstantB, ConstantC as C, ConstantD};use function MyApp\\function;
use const MyApp\OtherNamespace\ConstantE as ConstantF;use const MyApp\OtherNamespace\ConstantG;
use const MyApp\FirstConst, MyApp\AnotherNamespace\SecondConst;
use const \PHP_BINARY;

EOD;

        $php_file = new PhpFile($content);

        assert_true([
                'MyApp\OtherNamespace\ConstantA' => 'ConstantA',
                'MyApp\OtherNamespace\ConstantB' => 'ConstantB',
                'MyApp\OtherNamespace\ConstantC' => 'C',
                'MyApp\OtherNamespace\ConstantD' => 'ConstantD',
                'MyApp\OtherNamespace\ConstantE' => 'ConstantF',
                'MyApp\OtherNamespace\ConstantG' => 'ConstantG',
                'MyApp\FirstConst' => 'FirstConst',
                'MyApp\AnotherNamespace\SecondConst' => 'SecondConst',
                '\PHP_BINARY' => 'PHP_BINARY',
            ] === $php_file->imported_constants()
        );
    }
);
