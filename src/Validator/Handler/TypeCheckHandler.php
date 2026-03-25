<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Validator\Handler;

use ZJKiza\HttpResponseValidator\Contract\StructureValidationHandlerInterface;
use ZJKiza\HttpResponseValidator\Validator\Helper\ErrorCollector;
use ZJKiza\HttpResponseValidator\Validator\Helper\ValidationContext;

final class TypeCheckHandler implements StructureValidationHandlerInterface
{
    public function support(int|string $key, mixed $expected, array $data, string $currentPath, ValidationContext $context): bool
    {
        return $context->checkTypes && \is_string($expected) && \array_key_exists($key, $data);
    }

    public function handle(int|string $key, mixed $expected, array $data, string $currentPath, ErrorCollector $errorCollector, ValidationContext $context): bool
    {
        \assert(\is_string($expected));

        $actualValue = $data[$key];

        $expectedTypes = \explode('|', $expected);

        $errorTypes = [];

        foreach ($expectedTypes as $expectedType) {
            if ($context->typeChecker::isValid($expectedType, $actualValue)) {
                return true;
            }

            $errorTypes[] = $expectedType;
        }

        $errorCollector->add(\sprintf(
            'Key "%s.%s" expects type "%s", got "%s"',
            $currentPath,
            $key,
            \implode('|', $errorTypes),
            \gettype($actualValue)
        ));

        return true;
    }
}
