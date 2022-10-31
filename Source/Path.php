<?php

namespace Saeghe\Saeghe;

class Path
{
    private string $string;

    public function __construct(string $pathString)
    {
        $this->string = self::realPath(self::replaceSeparator($pathString));
    }

    public static function fromString(string $pathString): static
    {
        return new static($pathString);
    }

    public function toString(): string
    {
        return $this->string;
    }

    public static function replaceSeparator(string $pathString): string
    {
        $needle = DIRECTORY_SEPARATOR === '/' ? '\\' : '/';

        $pathString = str_replace($needle, DIRECTORY_SEPARATOR, $pathString);

        while (str_contains($pathString, DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR)) {
            $pathString = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $pathString);
        }

        return $pathString;
    }

    public static function realPath(string $pathString): string
    {
        $pathString = self::replaceSeparator($pathString);
        $pathString = str_replace(DIRECTORY_SEPARATOR . '.' . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $pathString);
        $pathString = Str\last_character($pathString) === DIRECTORY_SEPARATOR ? Str\remove_last_character($pathString) : $pathString;

        $parts = explode(DIRECTORY_SEPARATOR, $pathString);

        while (in_array('..', $parts)) {
            foreach ($parts as $key => $part) {
                if ($part === '..') {
                    unset($parts[$key - 1]);
                    unset($parts[$key]);
                    $parts = array_values($parts);
                    break;
                }
            }
        }


        return implode(DIRECTORY_SEPARATOR, $parts);
    }

    public function parent(): static
    {
        return new static(Str\before_last_occurrence($this->string, DIRECTORY_SEPARATOR));
    }

    public function append(string $pathString): static
    {
        return static::fromString($this->string . DIRECTORY_SEPARATOR . $pathString);
    }

    public function directory(): string
    {
        if (Str\last_character($this->string) !== DIRECTORY_SEPARATOR) {
            return $this->string . DIRECTORY_SEPARATOR;
        }

        return $this->string;
    }
}
