<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Handler;

use ZJKiza\HttpResponseValidator\Exception\InvalidArgumentException;
use ZJKiza\HttpResponseValidator\Monad\Result;

use function ZJKiza\HttpResponseValidator\addIdInMessage;

final class ValidateArrayKeyExistHandler extends AbstractHandler
{
    /**
     * @param array<string, mixed> $data
     *
     * @return Result<array<string, mixed>>
     */
    public function __invoke(array $data, string $key): Result
    {
        if (\array_key_exists($key, $data)) {
            /** @var Result<array<string, mixed>> */
            return Result::success($data);
        }

        $message = \sprintf(
            '%s [ValidateArrayKeyExistHandler] There is no required field "%s" in the array (%s).',
            addIdInMessage(),
            $key,
            \implode(', ', \array_keys($data))
        );

        /** @var Result<array<string, mixed>> */
        return $this->fail($message, InvalidArgumentException::class);
    }
}
