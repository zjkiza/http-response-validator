<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Validator\Helper;

final class TypeChecker
{
    public static function isValid(string $expectedType, mixed $value): bool
    {
        // expectedType[]
        if (\str_ends_with($expectedType, '[]')) {
            if (!\is_array($value)) {
                return false;
            }

            $innerType = \substr($expectedType, 0, -2);

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
            default => true, // unknown type = don't enforce
        };
    }
}
