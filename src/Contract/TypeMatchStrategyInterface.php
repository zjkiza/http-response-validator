<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Contract;

use ZJKiza\HttpResponseValidator\Validator\Helper\ErrorCollector;
use ZJKiza\HttpResponseValidator\Validator\Helper\ValidationContext;

interface TypeMatchStrategyInterface
{
    public function supports(mixed $expected): bool;

    public function validate(
        int|string $key,
        mixed $expected,
        mixed $actualValue,
        string $currentPath,
        ErrorCollector $errorCollector,
        ValidationContext $context,
    ): void;
}
