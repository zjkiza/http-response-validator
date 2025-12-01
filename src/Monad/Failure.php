<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Monad;

/**
 * @psalm-suppress InvalidReturnStatement
 * @psalm-suppress InvalidReturnType
 *
 * @template T
 *
 * @extends Result<T>
 */
final class Failure extends Result
{
    /**
     * @param T $value
     */
    public function __construct(
        mixed $value,
        private readonly ?\Throwable $exception = null,
    ) {
        parent::__construct($value);
    }

    /**
     * @template R
     *
     * @param callable(T): Result<R> $fn
     *
     * @return Result<R>
     */
    public function bind(callable $fn): Result
    {
        return $this;
    }

    /**
     * @return T
     */
    public function getOrThrow(): mixed
    {
        if ($this->exception instanceof \Throwable) {
            throw $this->exception;
        }

        return $this->value;
    }
}
