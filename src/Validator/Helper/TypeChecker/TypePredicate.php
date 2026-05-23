<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Validator\Helper\TypeChecker;

use ZJKiza\HttpResponseValidator\Enum\TypeCheck;

final class TypePredicate
{
    public static function matches(TypeCheck $type, mixed $value): bool
    {
        return match ($type) {
            TypeCheck::ARRAY => \is_array($value),
            TypeCheck::BOOL => \is_bool($value),
            TypeCheck::CALLABLE => \is_callable($value),
            TypeCheck::COUNTABLE => \is_countable($value),
            TypeCheck::FLOAT => \is_float($value),
            TypeCheck::INT => \is_int($value),
            TypeCheck::ITERABLE => \is_iterable($value),
            TypeCheck::NON_EMPTY_STRING => \is_string($value) && '' !== $value,
            TypeCheck::NULL => \is_null($value),
            TypeCheck::NUMERIC => \is_numeric($value),
            TypeCheck::OBJECT => \is_object($value),
            TypeCheck::RESOURCE => \is_resource($value),
            TypeCheck::SCALAR => \is_scalar($value),
            TypeCheck::STRING => \is_string($value),
            TypeCheck::MIXED => true,
        };
    }
}
