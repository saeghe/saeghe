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

    public function classSignature()
    {
        $signature = '';

        foreach ($this->lines as $line) {
            if ($signature === '' && $this->isClassSignature($line)) {
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

        return $signature;
    }

    public function usedConstants()
    {
        $constants = [];

        foreach ($this->lines as $codeLine) {
            if ($this->isClassSignature($codeLine)) {
                break;
            }

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
            if ($this->isClassSignature($codeLine)) {
                break;
            }
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
            if ($this->isClassSignature($codeLine)) {
                break;
            }

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

    public function extendedClasses()
    {
        $parents = [];
        $signature = $this->classSignature();

        if (strlen($signature) > 0 && str_contains($signature, ' extends ')) {
            $extends = trim(explode(' extends', $signature)[1]);
            $extends = Str\before_last_occurrence($extends, ' implements ');

            if (str_contains($extends, ',')) {
                $parents = explode(',', $extends);
            } else {
                $parents[] = $extends;
            }
        }

        return array_map(function ($parent) {
            return trim(str_replace('{', '', $parent));
        }, $parents);
    }

    public function implementedInterfaces()
    {
        $interfaces = [];
        $signature = $this->classSignature();


        if (strlen($signature) > 0 && str_contains($signature, ' implements ')) {
            $implements = trim(explode(' implements', $signature)[1]);
            $implements = Str\before_last_occurrence($implements, ' extends ');

            if (str_contains($implements, ',')) {
                $interfaces = explode(',', $implements);
            } else {
                $interfaces[] = $implements;
            }
        }

        return array_map(function ($interface) {
            return trim(str_replace('{', '', $interface));
        }, $interfaces);
    }

    public function usedTraits()
    {
        $traits = [];

        foreach ($this->lines as $line) {
            if (str_starts_with($line, '    use ')) {
                $statement = explode('    use', $line)[1];
                $statement = str_replace(';', '', $statement);
                if (str_contains($statement, ',')) {
                    $traits = array_merge($traits, explode(',', $statement));
                } else {
                    $traits[] = $statement;
                }
            }
        }

        return array_map(function ($usedTrait) {
            $usedTrait = trim($usedTrait);
            if (str_contains($usedTrait, '{')) {
                $usedTrait = trim(Str\before_first_occurrence($usedTrait, '{'));
            }

            return $usedTrait;
        }, $traits);
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

    private function isClassSignature($line)
    {
        return str_starts_with($line, 'class ')
        || str_starts_with($line, 'interface ')
        || str_starts_with($line, 'abstract class ')
        || str_starts_with($line, 'trait ');
    }
}
