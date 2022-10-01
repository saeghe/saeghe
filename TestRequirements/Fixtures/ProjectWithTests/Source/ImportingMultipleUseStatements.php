<?php

namespace ProjectWithTests;

use ProjectWithTests\SampleFile as AnotherFile, ProjectWithTests\SubDirectory\SimpleClass;

class ImportingMultipleUseStatements
{
    public function __construct()
    {
        AnotherFile\anImportantFunction();
    }
}
