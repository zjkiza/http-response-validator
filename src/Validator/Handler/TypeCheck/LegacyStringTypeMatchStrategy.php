<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Validator\Handler\TypeCheck;

use ZJKiza\HttpResponseValidator\Contract\TypeMatchStrategyInterface;
use ZJKiza\HttpResponseValidator\Validator\Helper\ErrorCollector;
use ZJKiza\HttpResponseValidator\Validator\Helper\TypeChecker\LegacyTypeParser;
use ZJKiza\HttpResponseValidator\Validator\Helper\ValidationContext;

final class LegacyStringTypeMatchStrategy implements TypeMatchStrategyInterface
{
    public function supports(mixed $expected): bool
    {
        return \is_string($expected);
    }

    public function validate(
        int|string $key,
        mixed $expected,
        mixed $actualValue,
        string $currentPath,
        ErrorCollector $errorCollector,
        ValidationContext $context,
    ): void {
        if (!\is_string($expected)) {
            return;
        }

        // TODO(remove-legacy-type-strings): remove this strategy after BC window closes.
        if (\is_array($actualValue) && LegacyTypeParser::isArrayOfNotation($expected)) {
            $innerType = LegacyTypeParser::innerType($expected);

            foreach ($actualValue as $index => $item) {
                if ($context->typeChecker::isValid($innerType, $item)) {
                    continue;
                }

                $errorCollector->add(\sprintf(
                    'Key "%s.%s[%s]" expects type "%s", got "%s"',
                    $currentPath,
                    $key,
                    $index,
                    $innerType,
                    \gettype($item)
                ));
            }

            return;
        }

        // TODO(remove-legacy-type-strings): replace this parsing with UnionExpectedType only.
        foreach (LegacyTypeParser::unionTypes($expected) as $expectedType) {
            if ($context->typeChecker::isValid($expectedType, $actualValue)) {
                return;
            }
        }

        $errorCollector->add(\sprintf(
            'Key "%s.%s" expects type "%s", got "%s"',
            $currentPath,
            $key,
            $expected,
            \gettype($actualValue)
        ));
    }
}
