<?php

namespace ProjectWithTests\SubDirectory;

use function ProjectWithTests\SampleFile\anImportantFunction;

class SimpleClass
{
    public function __construct()
    {
        anImportantFunction();
        str_replace('hello', 'hi', 'hello world');
    }
}
