<?php

namespace ProjectWithTests\CompoundNamespace;

use ProjectWithTests\CompoundNamespace\Foo as CompoundFoo;

class UseCompoundNamespace
{
    public function run()
    {
        $classFoo = (new CompoundFoo\ClassFoo());
        $classname = CompoundFoo\ClassBar::class;
        $boot = CompoundFoo\ClassBaz::boot();
    }
}
