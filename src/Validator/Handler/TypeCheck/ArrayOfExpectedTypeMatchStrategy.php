<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Validator\Handler\TypeCheck;

use ZJKiza\HttpResponseValidator\Contract\TypeMatchStrategyInterface;
use ZJKiza\HttpResponseValidator\Validator\Helper\ErrorCollector;
use ZJKiza\HttpResponseValidator\Validator\Helper\ValidationContext;
use ZJKiza\HttpResponseValidator\Validator\Type\ArrayOfExpectedType;

final class ArrayOfExpectedTypeMatchStrategy implements TypeMatchStrategyInterface
{
    public function supports(mixed $expected): bool
    {
        return $expected instanceof ArrayOfExpectedType;
    }

    public function validate(
        int|string $key,
        mixed $expected,
        mixed $actualValue,
        string $currentPath,
        ErrorCollector $errorCollector,
        ValidationContext $context,
    ): void {
        if (!$expected instanceof ArrayOfExpectedType) {
            return;
        }

        if (!\is_array($actualValue)) {
            $errorCollector->add(\sprintf(
                'Key "%s.%s" expects type "%s", got "%s"',
                $currentPath,
                $key,
                $expected->describe(),
                \gettype($actualValue)
            ));

            return;
        }

        foreach ($actualValue as $index => $item) {
            if ($context->typeChecker::isValid($expected->type(), $item)) {
                continue;
            }

            $errorCollector->add(\sprintf(
                'Key "%s.%s[%s]" expects type "%s", got "%s"',
                $currentPath,
                $key,
                $index,
                $expected->type()->value,
                \gettype($item)
            ));
        }
    }
}
