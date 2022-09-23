<?php

namespace Tests\PhpFileTest\NamespaceTest;

require_once __DIR__ . '/../../Source/Str.php';
require_once __DIR__ . '/../../Source/PhpFile.php';

use Saeghe\Saeghe\PhpFile;

test(
    title: 'it should return null when there is no namespace',
    case: function () {
        $content = <<<EOD
<?php

class MyClass
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert(null === $phpFile->namespace());
    }
);

test(
    title: 'it should detect namespace',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp;

class MyClass
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert('MyApp' === $phpFile->namespace());
    }
);

test(
    title: 'it should detect namespace for sub namespace',
    case: function () {
        $content = <<<EOD
<?php

namespace MyApp\SubNamespace;

class MyClass
{

}

EOD;

        $phpFile = new PhpFile($content);

        assert('MyApp\SubNamespace' === $phpFile->namespace());
    }
);
