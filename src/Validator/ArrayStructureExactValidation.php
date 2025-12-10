<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Validator;

use ZJKiza\HttpResponseValidator\Contract\StructureValidationHandlerInterface;
use ZJKiza\HttpResponseValidator\Contract\ValidationStrategy;
use ZJKiza\HttpResponseValidator\Validator\Helper\ErrorCollector;
use ZJKiza\HttpResponseValidator\Validator\Helper\RecursiveDescent;
use ZJKiza\HttpResponseValidator\Validator\Helper\TypeChecker;
use ZJKiza\HttpResponseValidator\Validator\Handler\WildcardHandler;
use ZJKiza\HttpResponseValidator\Validator\Handler\MissingKeyHandler;
use ZJKiza\HttpResponseValidator\Validator\Handler\NullCheckHandler;
use ZJKiza\HttpResponseValidator\Validator\Handler\TypeCheckHandler;
use ZJKiza\HttpResponseValidator\Validator\Helper\ValidationContext;

final readonly class ArrayStructureExactValidation implements ValidationStrategy
{
    /** @var StructureValidationHandlerInterface[] */
    private array $handlers;

    private RecursiveDescent $recursiveDescent;

    private ValidationContext $context;

    public function __construct(
        private ErrorCollector $errorCollector,
        private bool $ignoreNulls = false,
        private bool $checkTypes = false,
    ) {
        $this->handlers = [
            new WildcardHandler(),
            new MissingKeyHandler(),
            new NullCheckHandler(),
            new TypeCheckHandler(),
        ];

        $this->recursiveDescent = new RecursiveDescent(
            validateCallback: fn (array $structure, array $data, string $path) => $this->validate($structure, $data, $path),
        );

        $this->context = new ValidationContext(
            ignoreNulls: $this->ignoreNulls,
            checkTypes: $this->checkTypes,
            typeChecker: new TypeChecker(),
            validateCallback: fn (array $structure, array $data, string $path) => $this->validate($structure, $data, $path),
        );
    }

    /**
     * @param array<array-key, mixed> $structure
     * @param array<array-key, mixed> $data
     */
    public function validate(array $structure, array $data, string $currentPath = 'root'): void
    {
        $structure = $this->normalized($structure);
        $hasWildcard = $this->hasWildcard($structure, $data, $currentPath);

        foreach ($structure as $key => $expected) {

            foreach ($this->handlers as $handler) {
                if ($handler->support($key, $expected, $data, $currentPath, $this->context)) {
                    $handled = $handler->handle($key, $expected, $data, $currentPath, $this->errorCollector, $this->context);
                    if (false === $handled) {
                        break;
                    }
                }
            }

            $this->recursiveDescent->descend(
                allow: !$hasWildcard,
                data: $data,
                key: $key,
                expected: $expected,
                currentPath: $currentPath,
            );
        }
    }

    public function getErrorCollector(): ErrorCollector
    {
        return $this->errorCollector;
    }

    private function safeJson(mixed $value): string
    {
        $json = \json_encode($value);

        return false === $json ? 'null' : $json;
    }

    /**
     * @param array<array-key, mixed> $structure
     *
     * @return array<string, bool|mixed>
     */
    private function normalized(array $structure): array
    {
        $normalized = [];

        foreach ($structure as $key => $value) {
            if (\is_int($key)) {
                /** @var string $value */
                $normalized[$value] = true;
                continue;
            }
            $normalized[$key] = $value;
        }

        return $normalized;
    }

    /**
     * @param array<array-key, mixed> $structure
     * @param array<array-key, mixed> $data
     */
    private function hasWildcard(array $structure, array $data, string $currentPath): bool
    {
        $hasWildcard = \array_key_exists('*', $structure);
        if (!$hasWildcard) {
            // Strict/exact check for extra/missing keys
            $expectedKeys = \array_keys($structure);
            $actualKeys = \array_keys($data);
            \sort($expectedKeys);
            \sort($actualKeys);

            if ($expectedKeys !== $actualKeys) {
                $this->errorCollector->add(
                    \sprintf(
                        'Exact key mismatch at "%s". Expected: %s, got: %s',
                        $currentPath,
                        $this->safeJson($expectedKeys),
                        $this->safeJson($actualKeys)
                    )
                );
            }
        }

        return $hasWildcard;
    }
}
