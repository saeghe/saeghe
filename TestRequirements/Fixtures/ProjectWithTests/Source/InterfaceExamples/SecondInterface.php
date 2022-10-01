<?php

namespace ProjectWithTests\InterfaceExamples;

use ArrayAccess;
use ProjectWithTests\InterfaceExamples\InnerInterfaces\ExtendableInterface as ExtendInterface;

interface SecondInterface extends ExtendInterface, ArrayAccess
{

}
