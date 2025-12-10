<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Validator\Helper;

final readonly class RecursiveDescent
{
    public function __construct(
        private \Closure $validateCallback,
    ) {
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public function descend(
        bool $allow,
        array $data,
        string $key,
        mixed $expected,
        string $currentPath,
    ): void {
        if (!$allow) {
            return;
        }

        if (\array_key_exists($key, $data) && \is_array($expected)) {
            $path = \sprintf('%s.%s', $currentPath, $key);
            ($this->validateCallback)($expected, $data[$key], $path);
        }
    }
}
