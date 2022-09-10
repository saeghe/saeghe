<?php

namespace ProjectWithTests;

use ProjectWithTests\SubDirectory\{SimpleClass, ClassUseAnotherClass as Another};
use function ProjectWithTests\SubDirectory\Helper\{helper1 as anotherFunction, helper2};
use const ProjectWithTests\SubDirectory\Constants\{CONSTANT, RENAME as AnotherConstant};
