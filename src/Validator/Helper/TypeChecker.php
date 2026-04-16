<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Validator\Helper;

use ZJKiza\HttpResponseValidator\Exception\InvalidArgumentException;

final class TypeChecker
{
    private const SUPPORTED_TYPES = [
        'string',
        'int',
        'float',
        'bool',
        'array',
        'object',
        'null',
        'mixed',
    ];

    public static function isValid(string $expectedType, mixed $value): bool
    {
        // expectedType[]
        if (\str_ends_with($expectedType, '[]')) {
            if (!\is_array($value)) {
                return false;
            }

            $innerType = \substr($expectedType, 0, -2);

            if (!\in_array($innerType, self::SUPPORTED_TYPES, true)) {
                throw new InvalidArgumentException(\sprintf('Unsupported type "%s" in expected type "%s".', $innerType, $expectedType));
            }

            foreach ($value as $item) {
                if (!self::isValid($innerType, $item)) {
                    return false;
                }
            }

            return true;
        }

        return match ($expectedType) {
            'string' => \is_string($value),
            'int' => \is_int($value),
            'float' => \is_float($value),
            'bool' => \is_bool($value),
            'array' => \is_array($value),
            'object' => \is_object($value),
            'null' => \is_null($value),
            'mixed' => true,
            default => throw new InvalidArgumentException(\sprintf('Unsupported type "%s" in expected types "%s".', $expectedType, \implode(',', self::SUPPORTED_TYPES))),
        };
    }
}
