<?php

namespace Saeghe\Saeghe;

use Saeghe\Saeghe\Str;

class PhpFile
{
    public array $lines;

    public function __construct($content)
    {
        $this->lines = explode(PHP_EOL, $content);
    }

    public function usedConstants()
    {
        $constants = [];

        foreach ($this->lines as $codeLine) {
            $lines = explode(';', $codeLine);

            foreach ($lines as $line) {
                $line = trim($line);

                if (str_starts_with($line, 'use const ')) {
                    if (str_contains($line, '\{')) {
                        $parts = Str\between($line, '{', '}');
                        $parts = explode(',', $parts);
                        $commonPart = str_replace('use const ', '', explode('\{', $line)[0]);

                        foreach ($parts as $part) {
                            $part = trim($part);
                            $constants[] = "$commonPart\\$part";
                        }
                    } else {
                        $constants[] = str_replace('use const ', '', $line);
                    }
                }
            }
        }

        return $this->findUsesIn($constants);
    }

    public function usedFunctions()
    {
        $functions = [];

        foreach ($this->lines as $codeLine) {
            $lines = explode(';', $codeLine);

            foreach ($lines as $line) {
                $line = trim($line);

                if (str_starts_with($line, 'use function ')) {
                    if (str_contains($line, '\{')) {
                        $parts = Str\between($line, '{', '}');
                        $parts = explode(',', $parts);
                        $commonPart = str_replace('use function ', '', explode('\{', $line)[0]);

                        foreach ($parts as $part) {
                            $part = trim($part);
                            $functions[] = "$commonPart\\$part";
                        }
                    } else {
                        $functions[] = str_replace('use function ', '', $line);
                    }
                }
            }
        }

        return $this->findUsesIn($functions);
    }

    public function usedClasses()
    {
        $classes = [];

        foreach ($this->lines as $codeLine) {
            $lines = explode(';', $codeLine);

            foreach ($lines as $line) {
                $line = trim($line);

                if (str_starts_with($line, 'use const ') || str_starts_with($line, 'use function ')) {
                    continue;
                }

                if (str_starts_with($line, 'use ')) {
                    if (str_contains($line, '\{')) {
                        $parts = Str\between($line, '{', '}');
                        $parts = explode(',', $parts);
                        $commonPart = str_replace('use ', '', explode('\{', $line)[0]);

                        foreach ($parts as $part) {
                            $part = trim($part);
                            $classes[] = "$commonPart\\$part";
                        }
                    } else {
                        $classes[] = str_replace('use ', '', $line);
                    }
                }
            }
        }

        return $this->findUsesIn($classes);
    }

    public function parentClass()
    {
        $parent = null;
        $signature = '';

        foreach ($this->lines as $line) {
            if (
                $signature === ''
                && (
                    str_starts_with($line, 'class ')
                    || str_starts_with($line, 'interface ')
                    || str_starts_with($line, 'abstract class ')
                    || str_starts_with($line, 'trait ')
                )
            ) {
                $signature = $line;
            } else {
                if (
                    str_ends_with($signature, '{')
                    || str_ends_with($signature, '{' . PHP_EOL)
                ) {
                    break;
                } else {
                    $signature .= $line;
                }
            }
        }

        if (strlen($signature) > 0 && str_contains($signature, ' extends ')) {
            $parent = trim(explode(' extends', $signature)[1]);

            if (str_contains($parent, ' ')) {
                $parent = explode(' ', $parent)[0];
            }

            $parent = str_replace('{', '', $parent);
        }

        return $parent;
    }

    public function namespace()
    {
        $namespace = null;
        foreach ($this->lines as $line) {
            if (str_starts_with($line, 'namespace ')) {
                $namespace = str_replace('namespace ', '', str_replace(';', '', $line));
                break;
            }
        }

        return $namespace;
    }

    private function findUsesIn($useStatements)
    {
        $uses = [];

        array_walk($useStatements, function ($import) use (&$uses) {
            $unified = explode(',', $import);

            foreach ($unified as $separated) {
                $separated = trim($separated);
                if (str_contains($separated, ' as ')) {
                    [$separated, $alias] = explode(' as ', $separated);
                } else {
                    $alias = Str\after_last_occurrence($separated, '\\');
                }

                $uses[$separated] = $alias;
            }
        });

        return $uses;
    }
}
