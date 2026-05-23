<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Validator\Helper;

use ZJKiza\HttpResponseValidator\Contract\ExpectedTypeInterface;
use ZJKiza\HttpResponseValidator\Enum\TypeCheck;
use ZJKiza\HttpResponseValidator\Validator\Helper\TypeChecker\LegacyTypeParser;
use ZJKiza\HttpResponseValidator\Validator\Helper\TypeChecker\TypeNormalizer;
use ZJKiza\HttpResponseValidator\Validator\Helper\TypeChecker\TypePredicate;
use ZJKiza\HttpResponseValidator\Validator\Type\ArrayOfExpectedType;
use ZJKiza\HttpResponseValidator\Validator\Type\UnionExpectedType;

final class TypeChecker
{
    public static function isValid(string|TypeCheck|ExpectedTypeInterface $expectedType, mixed $value): bool
    {
        if ($expectedType instanceof ArrayOfExpectedType) {
            if (!\is_array($value)) {
                return false;
            }

            foreach ($value as $item) {
                if (!TypePredicate::matches($expectedType->type(), $item)) {
                    return false;
                }
            }

            return true;
        }

        if ($expectedType instanceof UnionExpectedType) {
            foreach ($expectedType->types() as $type) {
                if (TypePredicate::matches($type, $value)) {
                    return true;
                }
            }

            return false;
        }

        $normalizedType = TypeNormalizer::normalize($expectedType);

        if (LegacyTypeParser::isArrayOfNotation($normalizedType)) {
            if (!\is_array($value)) {
                return false;
            }

            $innerType = LegacyTypeParser::innerType($normalizedType);
            $enumType = TypeNormalizer::resolveEnumType($innerType, $normalizedType);

            foreach ($value as $item) {
                if (!TypePredicate::matches($enumType, $item)) {
                    return false;
                }
            }

            return true;
        }

        $enumType = TypeNormalizer::resolveEnumType($normalizedType, $normalizedType);

        return TypePredicate::matches($enumType, $value);
    }
}
