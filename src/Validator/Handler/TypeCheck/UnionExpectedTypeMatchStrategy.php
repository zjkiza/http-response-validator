<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Validator\Handler\TypeCheck;

use ZJKiza\HttpResponseValidator\Contract\TypeMatchStrategyInterface;
use ZJKiza\HttpResponseValidator\Validator\Helper\ErrorCollector;
use ZJKiza\HttpResponseValidator\Validator\Helper\ValidationContext;
use ZJKiza\HttpResponseValidator\Validator\Type\UnionExpectedType;

final class UnionExpectedTypeMatchStrategy implements TypeMatchStrategyInterface
{
    public function supports(mixed $expected): bool
    {
        return $expected instanceof UnionExpectedType;
    }

    public function validate(
        int|string $key,
        mixed $expected,
        mixed $actualValue,
        string $currentPath,
        ErrorCollector $errorCollector,
        ValidationContext $context,
    ): void {
        if (!$expected instanceof UnionExpectedType) {
            return;
        }

        if ($context->typeChecker::isValid($expected, $actualValue)) {
            return;
        }

        $errorCollector->add(\sprintf(
            'Key "%s.%s" expects type "%s", got "%s"',
            $currentPath,
            $key,
            $expected->describe(),
            \gettype($actualValue)
        ));
    }
}
