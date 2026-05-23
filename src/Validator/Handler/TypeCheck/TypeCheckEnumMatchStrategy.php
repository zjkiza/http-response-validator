<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Validator\Handler\TypeCheck;

use ZJKiza\HttpResponseValidator\Contract\TypeMatchStrategyInterface;
use ZJKiza\HttpResponseValidator\Enum\TypeCheck;
use ZJKiza\HttpResponseValidator\Validator\Helper\ErrorCollector;
use ZJKiza\HttpResponseValidator\Validator\Helper\ValidationContext;

final class TypeCheckEnumMatchStrategy implements TypeMatchStrategyInterface
{
    public function supports(mixed $expected): bool
    {
        return $expected instanceof TypeCheck;
    }

    public function validate(
        int|string $key,
        mixed $expected,
        mixed $actualValue,
        string $currentPath,
        ErrorCollector $errorCollector,
        ValidationContext $context,
    ): void {
        if (!$expected instanceof TypeCheck) {
            return;
        }

        if ($context->typeChecker::isValid($expected, $actualValue)) {
            return;
        }

        $errorCollector->add(\sprintf(
            'Key "%s.%s" expects type "%s", got "%s"',
            $currentPath,
            $key,
            $expected->value,
            \gettype($actualValue)
        ));
    }
}
