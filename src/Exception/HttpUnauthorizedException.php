<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Exception;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use ZJKiza\HttpResponseValidator\Contract\HttpResponseExceptionInterface;

final class HttpUnauthorizedException extends UnauthorizedHttpException implements HttpResponseExceptionInterface
{
    use HttpResponseExceptionTrait;

    /**
     * @param array<string, string|int> $requestFailed
     */
    public function __construct(string $message, string $messageId, array $requestFailed = [], ?\Throwable $previous = null)
    {
        parent::__construct("Bearer realm='API'", $message, $previous);

        $this->initializeHttpResponse($messageId, $requestFailed);
    }
}
