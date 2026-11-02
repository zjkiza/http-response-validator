<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\HttpLogger;

use ZJKiza\HttpResponseValidator\Exception\HttpBadRequestException;
use ZJKiza\HttpResponseValidator\Exception\HttpForbiddenException;
use ZJKiza\HttpResponseValidator\Exception\HttpNotFoundException;
use ZJKiza\HttpResponseValidator\Exception\HttpUnauthorizedException;
use ZJKiza\HttpResponseValidator\Exception\RuntimeException;

final class HttpExceptionFactory
{
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;

    /**
     * Separates the log message from the request details appended to the exception message.
     */
    public const DETAILS_SEPARATOR = ' | ';

    public const BODY_LIMIT = 2000;

    /**
     * @param array<string, string|int> $requestFailed data logged under the `http_request_failed` key
     */
    public function create(int $statusCode, string $message, string $messageId, array $requestFailed = []): \Throwable
    {
        $fullMessage = $this->appendRequestDetails($message, $requestFailed);

        return match ($statusCode) {
            self::HTTP_BAD_REQUEST => new HttpBadRequestException($fullMessage, $messageId, $requestFailed),
            self::HTTP_UNAUTHORIZED => new HttpUnauthorizedException($fullMessage, $messageId, $requestFailed),
            self::HTTP_FORBIDDEN => new HttpForbiddenException($fullMessage, $messageId, $requestFailed),
            self::HTTP_NOT_FOUND => new HttpNotFoundException($fullMessage, $messageId, $requestFailed),
            default => new RuntimeException($fullMessage, $statusCode),
        };
    }

    /**
     * The same data that is written to the log is added to the exception message,
     * so that the cause is visible in the error tracker without searching the logs.
     *
     * @param array<string, string|int> $requestFailed
     */
    private function appendRequestDetails(string $message, array $requestFailed): string
    {
        if ([] === $requestFailed) {
            return $message;
        }

        $body = (string) \preg_replace('/\s+/', ' ', \trim((string) ($requestFailed['body'] ?? '')));

        if (\mb_strlen($body) > self::BODY_LIMIT) {
            $body = \sprintf('%s... [truncated]', \mb_substr($body, 0, self::BODY_LIMIT));
        }

        return \sprintf(
            '%s%s%s %s%sResponse body: %s',
            \rtrim($message),
            self::DETAILS_SEPARATOR,
            (string) ($requestFailed['method'] ?? 'UNKNOWN'),
            (string) ($requestFailed['url'] ?? 'UNKNOWN'),
            self::DETAILS_SEPARATOR,
            $body,
        );
    }
}
