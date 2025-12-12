<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Handler\Factory;

use ZJKiza\HttpResponseValidator\Contract\HandlerFactoryInterface;
use ZJKiza\HttpResponseValidator\Contract\HandlerInterface;

/**
 * @template T of HandlerInterface<mixed, mixed>
 *
 * @implements HandlerFactoryInterface<T>
 */
final class HandlerFactory implements HandlerFactoryInterface
{
    /**
     * @var array<class-string<T>, T>
     */
    private array $handlers;

    /**
     * @param iterable<class-string<T>, T> $handlers
     */
    public function __construct(
        iterable $handlers,
    ) {
        $this->handlers = $handlers instanceof \Traversable ? \iterator_to_array($handlers, true) : $handlers;
    }

    /**
     * @param class-string<T> $class
     *
     * @return T
     */
    public function create(string $class): HandlerInterface
    {
        return $this->handlers[$class]
            ?? throw new \RuntimeException(\sprintf("Handler '%s' not found.", $class));
    }
}
