<?php

namespace Saeghe\Saeghe;

use Saeghe\Datatype\Str;

class PhpFile
{
    public string $content;
    public array $lines;

    public function __construct($content)
    {
        $this->content = $this->strip_content($content);
        $this->lines = explode(PHP_EOL, $this->content);
    }

    private function strip_content($content)
    {
        $new_content = '';
        $tokens = token_get_all($content);

        foreach ($tokens as $token) {
            if (is_string($token)) {
                // simple 1-character token
                $new_content .= $token;
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
                        $new_content .= $text;
                        break;
                }
            }
        }

        return $new_content;
    }

    public function class_signature()
    {
        $signature = '';

        foreach ($this->lines as $line) {
            if ($signature === '' && $this->is_class_signature($line)) {
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

    public function imported_constants()
    {
        $constants = [];

        foreach ($this->lines as $code_line) {
            if ($this->is_class_signature($code_line)) {
                break;
            }

            $lines = explode(';', $code_line);

            foreach ($lines as $line) {
                $line = trim($line);

                if (str_starts_with($line, 'use const ')) {
                    if (str_contains($line, '\{')) {
                        $parts = Str\between($line, '{', '}');
                        $parts = explode(',', $parts);
                        $common_part = str_replace('use const ', '', explode('\{', $line)[0]);

                        foreach ($parts as $part) {
                            $part = trim($part);
                            $constants[] = "$common_part\\$part";
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

    public function used_constants(): array
    {
        $imported_constants = $this->imported_constants();
        $imported_classes = $this->imported_classes();

        preg_match_all("/(\w+\\\\)*\w+::\w+\W/", $this->content, $used_constants, PREG_OFFSET_CAPTURE);

        $used_constants = array_filter($used_constants[0], function ($used_constant) {
            $used_constant = $used_constant[0];

            return ! str_starts_with($used_constant, 'class::')
                && ! str_starts_with($used_constant, 'self::')
                && ! str_starts_with($used_constant, 'parent::')
                && ! str_starts_with($used_constant, 'static::')
                && ! str_ends_with($used_constant, '(')
                && ! str_ends_with($used_constant, '::class' . Str\last_character($used_constant));
        });

        $in_class_used_constants = array_map(function ($used_constant) use ($imported_classes) {
            if (str_contains($this->content, '\\' . $used_constant[0])) {
                return Str\remove_last_character(str_replace('::', '\\', '\\' . $used_constant[0]));
            }

            $used_constant = Str\remove_last_character($used_constant[0]);

            [$class, $const] = explode('::', $used_constant);

            foreach ($imported_classes as $use => $alias) {
                if ($use === $class || $class === $alias) {
                    return $use . '\\' . $const;
                }

                if (str_starts_with($class, $alias . '\\')) {
                    $class = Str\replace_first_occurrence($class, $alias, $use);
                    return $class . '\\' . $const;
                }
            }

            return $this->namespace() . '\\' . str_replace('::', '\\', $used_constant);
        }, $used_constants);

        $constants_from_import = [];

        foreach ($imported_constants as $use => $alias) {
            if (preg_match_all("/\W$alias\W/", $this->content, $dont_care, PREG_OFFSET_CAPTURE) > 1) {
                $constants_from_import[] = $use;
            }
        }

        return array_unique(array_merge($in_class_used_constants, $constants_from_import));
    }

    public function imported_functions()
    {
        $functions = [];

        foreach ($this->lines as $code_line) {
            if ($this->is_class_signature($code_line)) {
                break;
            }
            $lines = explode(';', $code_line);

            foreach ($lines as $line) {
                $line = trim($line);

                if (str_starts_with($line, 'use function ')) {
                    if (str_contains($line, '\{')) {
                        $parts = Str\between($line, '{', '}');
                        $parts = explode(',', $parts);
                        $common_part = str_replace('use function ', '', explode('\{', $line)[0]);

                        foreach ($parts as $part) {
                            $part = trim($part);
                            $functions[] = "$common_part\\$part";
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

    public function used_functions()
    {
        $imported_functions = $this->imported_functions();
        $imported_classes = $this->imported_classes();

        $content = preg_replace("/ function \w+\(/", ' ', $this->content);
        $content = preg_replace("/new (\w+\\\\)*\w+\(/", ' ', $content);
        preg_match_all("/\W(\w+\\\\)*\w+\(/", $content, $used_functions, PREG_OFFSET_CAPTURE);

        $used_functions = array_filter($used_functions[0], function ($used_function) {
            return ! str_starts_with($used_function[0], '$')
                && ! str_starts_with($used_function[0], ':')
                && ! str_starts_with($used_function[0], '>');
        });

        $in_file_used_functions = array_map(function ($used_function) use ($imported_classes, $imported_functions) {
            $used_function = Str\remove_last_character($used_function[0]);

            if (str_starts_with($used_function, '\\')) {
                return $used_function;
            }

            $used_function = Str\remove_first_character($used_function);

            $function = str_contains($used_function, '\\')
                ? Str\after_last_occurrence($used_function, '\\')
                : $used_function;

            $file = str_contains($used_function, '\\')
                ? Str\before_last_occurrence($used_function, '\\')
                : '';

            foreach ($imported_classes as $class => $alias) {
                if ($alias === $file) {
                    return $class . '\\' . $function;
                }

                if (str_starts_with($file, "$alias\\")) {
                    return Str\replace_first_occurrence($file, $alias, $class) . '\\' . $function;
                }
            }

            if (in_array($function, array_values($imported_functions))) {
                return null;
            }

            if ($file === '') {
                return $function;
            }

            return $this->namespace() . '\\' . $used_function;
        }, $used_functions);

        $functions_from_import = [];

        foreach ($imported_functions as $use => $alias) {
            if ((int) preg_match_all("/\W$alias\(/", $content, $dont_care, PREG_OFFSET_CAPTURE) > 0) {
                $functions_from_import[] = $use;
            }
        }

        $used_functions = array_values(array_filter(array_unique(array_merge($in_file_used_functions, $functions_from_import))));

        return $used_functions;
    }

    public function imported_classes()
    {
        $classes = [];

        foreach ($this->lines as $code_line) {
            if ($this->is_class_signature($code_line)) {
                break;
            }
            $lines = explode(';', $code_line);

            foreach ($lines as $line) {
                $line = trim($line);

                if (str_starts_with($line, 'use const ') || str_starts_with($line, 'use function ')) {
                    continue;
                }

                if (str_starts_with($line, 'use ')) {
                    if (str_contains($line, '\{')) {
                        $parts = Str\between($line, '{', '}');
                        $parts = explode(',', $parts);
                        $common_part = str_replace('use ', '', explode('\{', $line)[0]);

                        foreach ($parts as $part) {
                            $part = trim($part);
                            $classes[] = "$common_part\\$part";
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

    public function used_classes()
    {
        $imported_classes = $this->imported_classes();

        preg_match_all("/(\w+\\\\)*\w+::\w+\(/", $this->content, $used_static_classes, PREG_OFFSET_CAPTURE);

        $used_static_classes = array_filter($used_static_classes[0], function ($used_static_class) {
            $used_static_class = Str\remove_last_character($used_static_class[0]);
            return ! str_contains($this->content, '\\' . $used_static_class . '(')
                && ! str_ends_with($used_static_class, '::class')
                && ! str_starts_with($used_static_class, 'parent')
                && ! str_starts_with($used_static_class, 'static')
                && ! str_starts_with($used_static_class, 'self');
        });

        $used_static_classes_in_file = array_map(function ($used_static_class) use ($imported_classes) {
            $used_static_class = Str\remove_last_character($used_static_class[0]);
            [$class, $method] = explode('::', $used_static_class);

            foreach ($imported_classes as $path => $alias) {
                if ($class === $alias) {
                    return $path;
                }

                if (str_starts_with($class, $alias . '\\')) {
                    return Str\replace_first_occurrence($class, $alias, $path);
                }
            }

            if (str_contains($class, '\\')) {
                return $class;
            }

            return $this->namespace() . '\\' . $class;
        }, $used_static_classes);

        preg_match_all("/new (\w+\\\\)*\w+/", $this->content, $used_instantiated_classes, PREG_OFFSET_CAPTURE);

        $used_instantiated_classes = array_filter($used_instantiated_classes[0], function ($used_instantiated_class) {
            return ! str_starts_with($used_instantiated_class[0], 'new self')
                && ! str_starts_with($used_instantiated_class[0], 'new parent')
                && ! str_starts_with($used_instantiated_class[0], 'new static');
        });

        $used_instantiated_classes_in_file = array_map(function ($used_instantiated_class) use ($imported_classes) {
            $used_instantiated_class = Str\replace_first_occurrence($used_instantiated_class[0], 'new ', '');

            $class = Str\after_last_occurrence($used_instantiated_class, '\\');
            $path = Str\before_last_occurrence($used_instantiated_class, '\\');

            foreach ($imported_classes as $imported_class => $alias) {
                if ($path === $imported_class || $path === $alias) {
                    return $imported_class . (strlen($class) > 0 ? '\\' . $class : '');
                }

                if ($this->alias_is_namespace($alias) && str_starts_with($class, $alias . '\\')) {
                    return Str\replace_first_occurrence($class, $alias, $path);
                }
            }

            if (str_contains($path, '\\')) {
                return $path . '\\' . $class;
            }

            return $this->namespace() . '\\' . $path;
        }, $used_instantiated_classes);

        return array_unique(array_merge($used_static_classes_in_file, $used_instantiated_classes_in_file));
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

    public function extended_classes()
    {
        $parents = [];
        $signature = $this->class_signature();

        if (strlen($signature) > 0 && str_contains($signature, ' extends ')) {
            $extends = trim(explode(' extends', $signature)[1]);
            $extends = Str\before_last_occurrence($extends, ' implements ');

            if (str_contains($extends, ',')) {
                $parents = explode(',', $extends);
            } else {
                $parents[] = $extends;
            }
        }

        $imported_classes = $this->imported_classes();

        return array_map(function ($parent) use ($imported_classes) {
            $parent = trim(str_replace('{', '', $parent));

            if (str_starts_with($parent, '\\')) {
                return $parent;
            }

            foreach ($imported_classes as $path => $alias) {
                if ($parent === $path || $parent === '\\' . $path) {
                    return $path;
                }

                if ($alias === $parent) {
                    return $path;
                }

                if (str_starts_with($parent, $alias . '\\')) {
                    return Str\replace_first_occurrence($parent, $alias, $path);
                }
            }

            return $this->namespace() . '\\' . $parent;
        }, $parents);
    }

    public function implemented_interfaces()
    {
        $interfaces = [];
        $signature = $this->class_signature();


        if (strlen($signature) > 0 && str_contains($signature, ' implements ')) {
            $implements = trim(explode(' implements', $signature)[1]);
            $implements = Str\before_last_occurrence($implements, ' extends ');

            if (str_contains($implements, ',')) {
                $interfaces = explode(',', $implements);
            } else {
                $interfaces[] = $implements;
            }
        }

        $imported_classes = $this->imported_classes();

        return array_map(function ($interface) use ($imported_classes) {
            $interface = trim(str_replace('{', '', $interface));

            if (str_starts_with($interface, '\\')) {
                return $interface;
            }

            foreach ($imported_classes as $path => $alias) {
                if ($interface === $path || $interface === '\\' . $path) {
                    return $path;
                }

                if ($alias === $interface) {
                    return $path;
                }

                if (str_starts_with($interface, $alias . '\\')) {
                    return Str\replace_first_occurrence($interface, $alias, $path);
                }
            }

            return $this->namespace() . '\\' . $interface;
        }, $interfaces);
    }

    public function used_traits()
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

        $imported_classes = $this->imported_classes();

        return array_map(function ($used_trait) use ($imported_classes) {
            $used_trait = trim($used_trait);
            if (str_contains($used_trait, '{')) {
                $used_trait = trim(Str\before_first_occurrence($used_trait, '{'));
            }

            if (str_starts_with($used_trait, '\\')) {
                return $used_trait;
            }

            foreach ($imported_classes as $path => $alias) {
                if ($used_trait === $path) {
                    return $used_trait;
                }

                if ($alias === $used_trait) {
                    return $path;
                }

                if (str_starts_with($used_trait, $alias . '\\')) {
                    return Str\replace_first_occurrence($used_trait, $alias, $path);
                }
            }
            return $this->namespace() . '\\' . $used_trait;
        }, $traits);
    }

    private function is_class_signature($line)
    {
        return str_starts_with($line, 'class ')
            || str_starts_with($line, 'interface ')
            || str_starts_with($line, 'abstract class ')
            || str_starts_with($line, 'final class ')
            || str_starts_with($line, 'trait ')
            || str_starts_with($line, 'enum ');
    }

    private function alias_is_namespace($alias)
    {
        preg_match_all("/new $alias\\\\\w+(\\\\\w+)*[^\w]/", $this->content, $new_instances, PREG_OFFSET_CAPTURE);

        preg_match_all("/[^\w]+$alias\\\\\w+(\\\\\w+)*::\w+/", $this->content, $static_calls, PREG_OFFSET_CAPTURE);

        return count(array_merge($new_instances[0], $static_calls[0])) > 0;
    }
}
