<?php

namespace ProjectWithTests\SampleFile;

use function ProjectWithTests\Service\helper;
use const ProjectWithTests\Service\HELPER_CONST;

class ClassUsesHelper
{
    public function action()
    {
        helper(HELPER_CONST);
    }
}
