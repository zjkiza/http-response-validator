<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Contract;

interface HttpResponseExceptionInterface extends ExceptionInterface
{
    /**
     * Returns the identifier in the form `Message ID=<hex>`, the same one that was written to the log.
     */
    public function getMessageId(): string;

    /**
     * Returns the data that was logged under the `http_request_failed` context key.
     *
     * @return array<string, string|int>
     */
    public function getRequestFailed(): array;
}
