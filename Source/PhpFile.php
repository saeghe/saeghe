<?php

namespace Saeghe\Saeghe;

require_once __DIR__ . '/Str.php';

use Saeghe\Saeghe\Str;

class PhpFile
{
    public string $content;
    public array $lines;

    public function __construct($content)
    {
        $this->content = $this->strip_content($content);
        $this->lines = explode(PHP_EOL, $this->content);
    }

    public function isOop()
    {
        foreach ($this->lines as $codeLine) {
            if ($this->isClassSignature($codeLine)) {
                return true;
            }
        }

        return false;
    }

    private function strip_content($content)
    {
        $newContent = '';
        $tokens = token_get_all($content);

        foreach ($tokens as $token) {
            if (is_string($token)) {
                // simple 1-character token
                $newContent .= $token;
            } else {
                // token array
                list($id, $text) = $token;

                switch ($id) {
                    case T_COMMENT:
                    case T_DOC_COMMENT:
                    case T_CONSTANT_ENCAPSED_STRING:
                    case T_ENCAPSED_AND_WHITESPACE:
                        break;
                    default:
                        // anything else -> output "as is"
                        $newContent .= $text;
                        break;
                }
            }
        }

        return $newContent;
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

    public function importedConstants()
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

    public function usedConstants(): array
    {
        $importedConstants = $this->importedConstants();
        $importedClasses = $this->importedClasses();

        preg_match_all("/(\w+\\\\)*\w+::\w+\W/", $this->content, $usedConstants, PREG_OFFSET_CAPTURE);

        $usedConstants = array_filter($usedConstants[0], function ($usedConstant) {
            $usedConstant = $usedConstant[0];

            return ! str_starts_with($usedConstant, 'class::')
                && ! str_ends_with($usedConstant, '(')
                && ! str_ends_with($usedConstant, '::class' . Str\last_character($usedConstant));
        });

        $inClassUsedConstants = array_map(function ($usedConstant) use ($importedClasses) {
            if (str_contains($this->content, '\\' . $usedConstant[0])) {
                return Str\remove_last_character(str_replace('::', '\\', '\\' . $usedConstant[0]));
            }

            $usedConstant = Str\remove_last_character($usedConstant[0]);

            if (str_starts_with($usedConstant, 'self::')
                || str_starts_with($usedConstant, 'static::')
                || str_starts_with($usedConstant, 'parent::')
            ) {
                return str_replace('::', '\\', $usedConstant);
            }

            [$class, $const] = explode('::', $usedConstant);

            foreach ($importedClasses as $use => $alias) {
                if ($use === $class || $class === $alias) {
                    return $use . '\\' . $const;
                }

                if (str_starts_with($class, $alias . '\\')) {
                    $class = Str\replace_first_occurrance($class, $alias, $use);
                    return $class . '\\' . $const;
                }
            }

            return $this->namespace() . '\\' . str_replace('::', '\\', $usedConstant);
        }, $usedConstants);

        $constantsFromImport = [];

        foreach ($importedConstants as $use => $alias) {
            if (preg_match_all("/\W$alias\W/", $this->content, $dontCare, PREG_OFFSET_CAPTURE) > 1) {
                $constantsFromImport[] = $use;
            }
        }

        return array_unique(array_merge($inClassUsedConstants, $constantsFromImport));
    }

    public function importedFunctions()
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

    public function usedFunctions()
    {
        $importedFunctions = $this->importedFunctions();
        $importedClasses = $this->importedClasses();

        $content = preg_replace("/ function \w+\(/", ' ', $this->content);
        $content = preg_replace("/new (\w+\\\\)*\w+\(/", ' ', $content);
        preg_match_all("/\W(\w+\\\\)*\w+\(/", $content, $usedFunctions, PREG_OFFSET_CAPTURE);

        $usedFunctions = array_filter($usedFunctions[0], function ($usedFunction) {
            return ! str_starts_with($usedFunction[0], '$')
                && ! str_starts_with($usedFunction[0], ':')
                && ! str_starts_with($usedFunction[0], '>');
        });

        $inFileUsedFunctions = array_map(function ($usedFunction) use ($importedClasses, $importedFunctions) {
            $usedFunction = Str\remove_last_character($usedFunction[0]);

            if (str_starts_with($usedFunction, '\\')) {
                return $usedFunction;
            }

            $usedFunction = Str\remove_first_character($usedFunction);

            $function = str_contains($usedFunction, '\\')
                ? Str\after_last_occurrence($usedFunction, '\\')
                : $usedFunction;

            $file = str_contains($usedFunction, '\\')
                ? Str\before_last_occurrence($usedFunction, '\\')
                : '';

            foreach ($importedClasses as $class => $alias) {
                if ($alias === $file) {
                    return $class . '\\' . $function;
                }

                if (str_starts_with($file, "$alias\\")) {
                    return Str\replace_first_occurrance($file, $alias, $class) . '\\' . $function;
                }
            }

            if (in_array($function, array_values($importedFunctions))) {
                return null;
            }

            if ($file === '') {
                return $function;
            }

            return $this->namespace() . '\\' . $usedFunction;
        }, $usedFunctions);

        $functionsFromImport = [];

        foreach ($importedFunctions as $use => $alias) {
            if ((int) preg_match_all("/\W$alias\(/", $content, $dontCare, PREG_OFFSET_CAPTURE) > 0) {
                $functionsFromImport[] = $use;
            }
        }

        $usedFunctions = array_values(array_filter(array_unique(array_merge($inFileUsedFunctions, $functionsFromImport))));

        return $usedFunctions;
    }

    public function importedClasses()
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

        uksort($uses, fn ($first, $second) => strlen($second) - strlen($first));

        return $uses;
    }

    public function usedClasses()
    {
        $importedClasses = $this->importedClasses();

        preg_match_all("/(\w+\\\\)*\w+::\w+\(/", $this->content, $usedStaticClasses, PREG_OFFSET_CAPTURE);

        $usedStaticClasses = array_filter($usedStaticClasses[0], function ($usedStaticClass) {
            $usedStaticClass = Str\remove_last_character($usedStaticClass[0]);
            return ! str_contains($this->content, '\\' . $usedStaticClass . '(')
                && ! str_ends_with($usedStaticClass, '::class')
                && ! str_starts_with($usedStaticClass, 'parent')
                && ! str_starts_with($usedStaticClass, 'static')
                && ! str_starts_with($usedStaticClass, 'self');
        });

        $usedStaticClassesInFile = array_map(function ($usedStaticClass) use ($importedClasses) {
            $usedStaticClass = Str\remove_last_character($usedStaticClass[0]);
            [$class, $method] = explode('::', $usedStaticClass);

            foreach ($importedClasses as $path => $alias) {
                if ($class === $alias) {
                    return $path;
                }

                if (str_starts_with($class, $alias . '\\')) {
                    return Str\replace_first_occurrance($class, $alias, $path);
                }
            }

            if (str_contains($class, '\\')) {
                return $class;
            }

            return $this->namespace() . '\\' . $class;
        }, $usedStaticClasses);

        preg_match_all("/new (\w+\\\\)*\w+/", $this->content, $usedInstantiatedClasses, PREG_OFFSET_CAPTURE);

        $usedInstantiatedClasses = array_filter($usedInstantiatedClasses[0], function ($usedInstantiatedClass) {
            return ! str_starts_with($usedInstantiatedClass[0], 'new self')
                && ! str_starts_with($usedInstantiatedClass[0], 'new parent')
                && ! str_starts_with($usedInstantiatedClass[0], 'new static');
        });

        $usedInstantiatedClassesInFile = array_map(function ($usedInstantiatedClass) use ($importedClasses) {
            $usedInstantiatedClass = Str\replace_first_occurrance($usedInstantiatedClass[0], 'new ', '');

            $class = Str\after_last_occurrence($usedInstantiatedClass, '\\');
            $path = Str\before_last_occurrence($usedInstantiatedClass, '\\');

            foreach ($importedClasses as $importedClass => $alias) {
                if ($path === $importedClass || $path === $alias) {
                    return $importedClass . (strlen($class) > 0 ? '\\' . $class : '');
                }

                if ($this->aliasIsNamespace($alias) && str_starts_with($class, $alias . '\\')) {
                    return Str\replace_first_occurrance($class, $alias, $path);
                }
            }

            if (str_contains($path, '\\')) {
                return $path . '\\' . $class;
            }

            return $this->namespace() . '\\' . $path;
        }, $usedInstantiatedClasses);

        return array_unique(array_merge($usedStaticClassesInFile, $usedInstantiatedClassesInFile));
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

        $importedClasses = $this->importedClasses();

        return array_map(function ($parent) use ($importedClasses) {
            $parent = trim(str_replace('{', '', $parent));

            if (str_starts_with($parent, '\\')) {
                return $parent;
            }

            foreach ($importedClasses as $path => $alias) {
                if ($parent === $path || $parent === '\\' . $path) {
                    return $path;
                }

                if ($alias === $parent) {
                    return $path;
                }

                if (str_starts_with($parent, $alias . '\\')) {
                    return Str\replace_first_occurrance($parent, $alias, $path);
                }
            }

            return $this->namespace() . '\\' . $parent;
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

        $importedClasses = $this->importedClasses();

        return array_map(function ($interface) use ($importedClasses) {
            $interface = trim(str_replace('{', '', $interface));

            if (str_starts_with($interface, '\\')) {
                return $interface;
            }

            foreach ($importedClasses as $path => $alias) {
                if ($interface === $path || $interface === '\\' . $path) {
                    return $path;
                }

                if ($alias === $interface) {
                    return $path;
                }

                if (str_starts_with($interface, $alias . '\\')) {
                    return Str\replace_first_occurrance($interface, $alias, $path);
                }
            }

            return $this->namespace() . '\\' . $interface;
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

        $importedClasses = $this->importedClasses();

        return array_map(function ($usedTrait) use ($importedClasses) {
            $usedTrait = trim($usedTrait);
            if (str_contains($usedTrait, '{')) {
                $usedTrait = trim(Str\before_first_occurrence($usedTrait, '{'));
            }

            foreach ($importedClasses as $path => $alias) {
                if ($usedTrait === $path) {
                    return $usedTrait;
                }

                if ($alias === $usedTrait) {
                    return $path;
                }

                if (str_starts_with($usedTrait, $alias . '\\')) {
                    return Str\replace_first_occurrance($usedTrait, $alias, $path);
                }
            }
            return $this->namespace() . '\\' . $usedTrait;
        }, $traits);
    }

    private function isClassSignature($line)
    {
        return str_starts_with($line, 'class ')
            || str_starts_with($line, 'interface ')
            || str_starts_with($line, 'abstract class ')
            || str_starts_with($line, 'final class ')
            || str_starts_with($line, 'trait ')
            || str_starts_with($line, 'enum ');
    }

    private function aliasIsNamespace($alias)
    {
        preg_match_all("/new $alias\\\\\w+(\\\\\w+)*[^\w]/", $this->content, $newInstances, PREG_OFFSET_CAPTURE);

        preg_match_all("/[^\w]+$alias\\\\\w+(\\\\\w+)*::\w+/", $this->content, $staticCalls, PREG_OFFSET_CAPTURE);

        return count(array_merge($newInstances[0], $staticCalls[0])) > 0;
    }
}
