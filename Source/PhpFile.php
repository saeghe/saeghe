<?php

namespace Saeghe\Saeghe;

use Saeghe\Saeghe\Str;

class PhpFile
{
    public string $content;
    public array $lines;

    public function __construct($content)
    {
        $this->content = $content;
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

        $uses = [];

        array_walk($constants, function ($import) use (&$uses) {
            $unified = explode(',', $import);

            foreach ($unified as $import) {
                $import = trim($import);
                if (str_contains($import, ' as ')) {
                    [$import, $alias] = explode(' as ', $import);
                } else {
                    $alias = Str\after_last_occurrence($import, '\\');
                }

                $uses[$import] = $alias;
            }
        });

        return $uses;
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

        $uses = [];

        array_walk($functions, function ($import) use (&$uses) {
            $unified = explode(',', $import);

            foreach ($unified as $import) {
                $import = trim($import);
                if (str_contains($import, ' as ')) {
                    [$import, $alias] = explode(' as ', $import);
                } else {
                    $alias = Str\after_last_occurrence($import, '\\');
                }

                $uses[$import] = $alias;
            }
        });

        return $uses;
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

        $uses = [];

        array_walk($classes, function ($import) use (&$uses) {
            $commaSeparatedImports = explode(',', $import);

            foreach ($commaSeparatedImports as $commaSeparatedImport) {
                $commaSeparatedImport = trim($commaSeparatedImport);
                if (str_contains($commaSeparatedImport, ' as ')) {
                    [$aliasLink, $alias] = explode(' as ', $commaSeparatedImport);

                    $namespaceUsedClasses = $this->namespaceUsedClasses($alias);

                    if (count($namespaceUsedClasses) > 0) {
                        foreach ($namespaceUsedClasses as $namespaceUsedClass) {
                            $usedClass = str_replace($alias, $aliasLink, $namespaceUsedClass);
                            $uses[$usedClass] = Str\after_last_occurrence($namespaceUsedClass, '\\');
                        }
                    } else {
                        $uses[$aliasLink] = $alias;
                    }
                } else {
                    $alias = Str\after_last_occurrence($commaSeparatedImport, '\\');
                    $uses[$commaSeparatedImport] = $alias;
                }
            }
        });

        return $uses;
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

    private function isClassSignature($line)
    {
        return str_starts_with($line, 'class ')
            || str_starts_with($line, 'interface ')
            || str_starts_with($line, 'abstract class ')
            || str_starts_with($line, 'trait ');
    }

    private function namespaceUsedClasses($namespace)
    {
        $regex = "/$namespace+(\\\\\w+)+(\(|::\w+)/";

        preg_match_all($regex, $this->content, $matches, PREG_OFFSET_CAPTURE);

        $matches = array_filter($matches[0], fn ($usage) => ! str_ends_with($usage[0], '::class'));

        return array_map(function ($match) {
            $class = $match[0];

            if (str_contains($class, '(')) {
                return Str\before_first_occurrence($class, '(');
            }

            if (str_contains($class, '::')) {
                return Str\before_first_occurrence($class, '::');
            }

            return $class;
        }, $matches);
    }
}
