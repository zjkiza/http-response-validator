<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Exception;

/**
 * @internal
 */
trait HttpResponseExceptionTrait
{
    private string $messageId = '';

    /**
     * @var array<string, string|int>
     */
    private array $requestFailed = [];

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @return array<string, string|int>
     */
    public function getRequestFailed(): array
    {
        return $this->requestFailed;
    }

    /**
     * @param array<string, string|int> $requestFailed
     */
    private function initializeHttpResponse(string $messageId, array $requestFailed): void
    {
        $this->messageId = \rtrim(\trim($messageId), ': ');
        $this->requestFailed = $requestFailed;
    }
}
