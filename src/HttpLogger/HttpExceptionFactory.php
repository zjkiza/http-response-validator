<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\HttpLogger;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use ZJKiza\HttpResponseValidator\Exception\RuntimeException;

final class HttpExceptionFactory
{
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;

    public function create(int $statusCode, string $message, string $messageId): \Throwable
    {
        return match ($statusCode) {
            self::HTTP_BAD_REQUEST => new BadRequestHttpException(\sprintf('%s %s', $messageId, 'Bad request.')),
            self::HTTP_UNAUTHORIZED => new UnauthorizedHttpException("Bearer realm='API'", \sprintf('%s %s', $messageId, 'Invalid credentials')),
            self::HTTP_FORBIDDEN => new AccessDeniedHttpException(\sprintf('%s %s', $messageId, 'Access denied.')),
            self::HTTP_NOT_FOUND => new NotFoundHttpException(\sprintf('%s %s', $messageId, 'Not found.')),
            default => new RuntimeException($message, $statusCode),
        };
    }
}
