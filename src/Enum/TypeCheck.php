<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Enum;

enum TypeCheck: string
{
    case ARRAY = 'array';
    case BOOL = 'bool';
    case CALLABLE = 'callable';
    case COUNTABLE = 'countable';
    case FLOAT = 'float';
    case INT = 'int';
    case ITERABLE = 'iterable';
    case NON_EMPTY_STRING = 'non-empty-string';
    case NULL = 'null';
    case NUMERIC = 'numeric';
    case OBJECT = 'object';
    case RESOURCE = 'resource';
    case SCALAR = 'scalar';
    case STRING = 'string';
    case MIXED = 'mixed';

    public static function fromInput(string|self $type): self
    {
        if ($type instanceof self) {
            return $type;
        }

        $enum = self::tryFrom($type);

        if ($enum instanceof self) {
            return $enum;
        }

        throw new \InvalidArgumentException($type);
    }

    /** @return list<string> */
    public static function values(): array
    {
        /** @var list<string> $values */
        $values = \array_column(self::cases(), 'value');

        return $values;
    }
}
