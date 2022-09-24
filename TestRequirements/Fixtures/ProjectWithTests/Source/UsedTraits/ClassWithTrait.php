<?php

namespace ProjectWithTests\UsedTraits;

use ProjectWithTests\SubDirectory\SimpleClass;

class ClassWithTrait extends SimpleClass
{
    use FirstTrait, ThirdTrait;
}
