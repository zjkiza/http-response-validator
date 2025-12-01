<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Contract;

/**
 * @template T of HandlerInterface<mixed, mixed>
 */
interface HandlerFactoryInterface
{
    /**
     * @param class-string<T> $class
     *
     * @return T
     */
    public function create(string $class): HandlerInterface;
}
