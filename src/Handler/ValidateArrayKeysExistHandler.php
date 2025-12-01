<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Handler;

use ZJKiza\HttpResponseValidator\Contract\HandlerInterface;
use ZJKiza\HttpResponseValidator\Exception\InvalidPropertyValueException;
use ZJKiza\HttpResponseValidator\Handler\Factory\TagIndexMethod;
use ZJKiza\HttpResponseValidator\Monad\Failure;
use ZJKiza\HttpResponseValidator\Monad\Result;

/**
 * @implements HandlerInterface<array<string, mixed>, array<string, mixed>>
 */
final class ValidateArrayKeysExistHandler extends AbstractHandler implements HandlerInterface
{
    use TagIndexMethod;

    /** @var string[] */
    private array $keys = [];

    /**
     * @param array<string, mixed> $value
     *
     * @return Result<array<string, mixed>>
     */
    public function __invoke(mixed $value): Result
    {
        if (false === (bool) $this->keys) {
            throw new InvalidPropertyValueException('Property keys is not set in ValidateArrayKeysExistHandler.');
        }

        foreach ($this->keys as $key) {
            $result = (new ValidateArrayKeyExistHandler($this->logger))($value, $key);

            if ($result instanceof Failure) {
                return $result;
            }
        }

        return Result::success($value);
    }

    /**
     * @param string[] $keys
     */
    public function setKeys(array $keys): self
    {
        $this->keys = $keys;

        return $this;
    }
}
