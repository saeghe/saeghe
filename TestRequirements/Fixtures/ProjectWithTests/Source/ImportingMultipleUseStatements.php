<?php

namespace ProjectWithTests;

use ProjectWithTests\SampleFile as AnotherFile, ProjectWithTests\SubDirectory\SimpleClass;

function call()
{
    AnotherFile\anImportantFunction();

    $x = AnotherFile\anImportantFunction() || new SimpleClass();
}
