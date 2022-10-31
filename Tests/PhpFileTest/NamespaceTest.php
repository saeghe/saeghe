<?php

namespace Tests\PhpFileTest\NamespaceTest;

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

        $php_file = new PhpFile($content);

        assert(null === $php_file->namespace());
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

        $php_file = new PhpFile($content);

        assert('MyApp' === $php_file->namespace());
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

        $php_file = new PhpFile($content);

        assert('MyApp\SubNamespace' === $php_file->namespace());
    }
);
