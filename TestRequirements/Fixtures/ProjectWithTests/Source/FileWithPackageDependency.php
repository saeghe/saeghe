<?php

namespace ProjectWithTests\SampleFile;

use Saeghe\SimplePackage\AUsefulClass;

class FileWithPackageDependency
{
    public function __construct()
    {
        $this->dependency = new AUsefulClass();
    }
}
