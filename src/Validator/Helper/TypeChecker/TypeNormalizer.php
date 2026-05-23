<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Validator\Helper\TypeChecker;

use ZJKiza\HttpResponseValidator\Contract\ExpectedTypeInterface;
use ZJKiza\HttpResponseValidator\Enum\TypeCheck;
use ZJKiza\HttpResponseValidator\Exception\InvalidArgumentException;

final class TypeNormalizer
{
    public static function normalize(string|TypeCheck|ExpectedTypeInterface $expectedType): string
    {
        if ($expectedType instanceof TypeCheck) {
            return $expectedType->value;
        }

        if ($expectedType instanceof ExpectedTypeInterface) {
            return $expectedType->describe();
        }

        return $expectedType;
    }

    public static function resolveEnumType(string $type, string $fullType): TypeCheck
    {
        try {
            return TypeCheck::fromInput($type);
        } catch (\InvalidArgumentException) {
            throw new InvalidArgumentException(\sprintf('Unsupported type "%s" in expected type "%s". Supported types: "%s".', $type, $fullType, \implode(',', TypeCheck::values())));
        }
    }
}
