<?php

namespace Saeghe\Saeghe\DataType;

abstract class Text implements \Stringable
{
    private string $string;

    public function __construct(?string $init = null)
    {
        $this->set($init ?: '');
    }

    abstract public function is_valid(string $string): bool;

    public function set(string $string): static
    {
        if (! $this->is_valid($string)) {
            throw new \InvalidArgumentException('Invalid string passed to text.');
        }

        $this->string = $string;

        return $this;
    }

    public function string(): string
    {
        return $this->string;
    }

    public function __toString(): string
    {
        return $this->string();
    }
}
