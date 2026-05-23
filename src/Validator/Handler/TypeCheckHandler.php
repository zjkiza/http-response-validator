<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Validator\Handler;

use ZJKiza\HttpResponseValidator\Contract\ExpectedTypeInterface;
use ZJKiza\HttpResponseValidator\Contract\StructureValidationHandlerInterface;
use ZJKiza\HttpResponseValidator\Contract\TypeMatchStrategyInterface;
use ZJKiza\HttpResponseValidator\Enum\TypeCheck;
use ZJKiza\HttpResponseValidator\Validator\Handler\TypeCheck\ArrayOfExpectedTypeMatchStrategy;
use ZJKiza\HttpResponseValidator\Validator\Handler\TypeCheck\LegacyStringTypeMatchStrategy;
use ZJKiza\HttpResponseValidator\Validator\Handler\TypeCheck\TypeCheckEnumMatchStrategy;
use ZJKiza\HttpResponseValidator\Validator\Handler\TypeCheck\UnionExpectedTypeMatchStrategy;
use ZJKiza\HttpResponseValidator\Validator\Helper\ErrorCollector;
use ZJKiza\HttpResponseValidator\Validator\Helper\ValidationContext;

final readonly class TypeCheckHandler implements StructureValidationHandlerInterface
{
    /** @var list<TypeMatchStrategyInterface> */
    private array $strategies;

    public function __construct()
    {
        $this->strategies = [
            new TypeCheckEnumMatchStrategy(),
            new ArrayOfExpectedTypeMatchStrategy(),
            new UnionExpectedTypeMatchStrategy(),
            new LegacyStringTypeMatchStrategy(),
        ];
    }

    public function support(int|string $key, mixed $expected, array $data, string $currentPath, ValidationContext $context): bool
    {
        return $context->checkTypes
            && (\is_string($expected) || $expected instanceof TypeCheck || $expected instanceof ExpectedTypeInterface)
            && \array_key_exists($key, $data);
    }

    public function handle(int|string $key, mixed $expected, array $data, string $currentPath, ErrorCollector $errorCollector, ValidationContext $context): bool
    {
        $actualValue = $data[$key];

        foreach ($this->strategies as $strategy) {
            if (!$strategy->supports($expected)) {
                continue;
            }

            $strategy->validate($key, $expected, $actualValue, $currentPath, $errorCollector, $context);
            break;
        }

        return true;
    }
}
