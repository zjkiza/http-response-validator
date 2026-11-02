<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use ZJKiza\HttpResponseValidator\Contract\HttpResponseExceptionInterface;

final class HttpForbiddenException extends AccessDeniedHttpException implements HttpResponseExceptionInterface
{
    use HttpResponseExceptionTrait;

    /**
     * @param array<string, string|int> $requestFailed
     */
    public function __construct(string $message, string $messageId, array $requestFailed = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, $previous);

        $this->initializeHttpResponse($messageId, $requestFailed);
    }
}
