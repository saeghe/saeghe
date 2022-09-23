<?php

namespace ProjectWithTests\InterfaceExamples;

use ProjectWithTests\InterfaceExamples\InnerInterfaces\OtherInnerInterface;
use ProjectWithTests\SubDirectory\SimpleClass;

class MyClass extends SimpleClass implements FirstInterface, ThirdInterface, OtherInnerInterface
{

}
