<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Monad;

/**
 * @psalm-suppress ImplementedReturnTypeMismatch
 *
 * @template T
 *
 * @extends Result<T>
 */
final class Success extends Result
{
    /**
     * @template R
     *
     * @param callable(T): Result<R> $fn
     *
     * @return Result<R>|Result<\Throwable>
     */
    public function bind(callable $fn): Result
    {
        try {
            /** @var Result<R> $result */
            $result = $fn($this->value);

            return $result;
        } catch (\Throwable $e) {
            /** @retrun Result<\Throwable> */
            return new Failure($e, $e);
        }
    }

    /**
     * @return T
     */
    public function getOrThrow(): mixed
    {
        return $this->value;
    }
}
