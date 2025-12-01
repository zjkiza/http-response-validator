<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Contract;

use ZJKiza\HttpResponseValidator\Monad\Result;

/**
 * @template TInput
 * @template TOutput
 *
 * @psalm-type HandlerCallable = callable(TInput): Result<TOutput>
 *
 * @psalm-suppress MissingTemplateParam
 */
interface HandlerInterface
{
    public static function getIndex(): string;

    /**
     * @param TInput $value
     *
     * @return Result<TOutput>
     */
    public function __invoke(mixed $value): Result/* TOutput */;
}
