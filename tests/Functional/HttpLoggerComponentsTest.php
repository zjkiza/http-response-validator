<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use ZJKiza\HttpResponseValidator\Contract\ExceptionInterface;
use ZJKiza\HttpResponseValidator\Contract\HttpResponseExceptionInterface;
use ZJKiza\HttpResponseValidator\Exception\HttpBadRequestException;
use ZJKiza\HttpResponseValidator\Exception\HttpForbiddenException;
use ZJKiza\HttpResponseValidator\Exception\HttpNotFoundException;
use ZJKiza\HttpResponseValidator\Exception\HttpUnauthorizedException;
use ZJKiza\HttpResponseValidator\Exception\RuntimeException;
use ZJKiza\HttpResponseValidator\HttpLogger\HttpExceptionFactory;
use ZJKiza\HttpResponseValidator\HttpLogger\ResponseBodyFormatter;
use ZJKiza\HttpResponseValidator\Tests\Resources\KernelTestCase;

final class HttpLoggerComponentsTest extends KernelTestCase
{
    private const MESSAGE_ID = 'Message ID=abc123 : ';

    /**
     * @param class-string $expectedClass
     * @param class-string $expectedSymfonyParent
     */
    #[DataProvider('provideExceptionFactoryCases')]
    public function testHttpExceptionFactoryCreate(int $statusCode, string $expectedClass, string $expectedSymfonyParent): void
    {
        $factory = new HttpExceptionFactory();

        $exception = $factory->create($statusCode, self::MESSAGE_ID.'[HttpRequestLogger ERROR CODE] Unexpected status code', self::MESSAGE_ID, $this->requestFailed());
        \restore_exception_handler();

        self::assertInstanceOf($expectedClass, $exception);
        self::assertInstanceOf($expectedSymfonyParent, $exception);
        self::assertInstanceOf(ExceptionInterface::class, $exception);
        self::assertInstanceOf(HttpResponseExceptionInterface::class, $exception);
        self::assertSame('Message ID=abc123', $exception->getMessageId());
        self::assertSame($this->requestFailed(), $exception->getRequestFailed());
    }

    public static function provideExceptionFactoryCases(): \Generator
    {
        yield '400 bad request' => [400, HttpBadRequestException::class, BadRequestHttpException::class];
        yield '401 unauthorized' => [401, HttpUnauthorizedException::class, UnauthorizedHttpException::class];
        yield '403 forbidden' => [403, HttpForbiddenException::class, AccessDeniedHttpException::class];
        yield '404 not found' => [404, HttpNotFoundException::class, NotFoundHttpException::class];
    }

    public function testHttpExceptionFactoryFallsBackToRuntimeException(): void
    {
        $factory = new HttpExceptionFactory();

        $exception = $factory->create(500, self::MESSAGE_ID.'[HttpRequestLogger ERROR CODE] Unexpected status code', self::MESSAGE_ID, $this->requestFailed());
        \restore_exception_handler();

        self::assertInstanceOf(RuntimeException::class, $exception);
        self::assertSame(500, $exception->getCode());
        self::assertSame(
            'Message ID=abc123 : [HttpRequestLogger ERROR CODE] Unexpected status code | GET https://example.com/api | Response body: {"error": "invalid token"}',
            $exception->getMessage()
        );
    }

    public function testHttpExceptionMessageContainsLoggedMessageAndRequestDetails(): void
    {
        $factory = new HttpExceptionFactory();

        $exception = $factory->create(400, self::MESSAGE_ID.'[HttpRequestLogger ERROR CODE] Unexpected status code 400 expected 200', self::MESSAGE_ID, $this->requestFailed());
        \restore_exception_handler();

        self::assertSame(
            'Message ID=abc123 : [HttpRequestLogger ERROR CODE] Unexpected status code 400 expected 200 | GET https://example.com/api | Response body: {"error": "invalid token"}',
            $exception->getMessage()
        );
    }

    public function testHttpExceptionMessageTruncatesLongBody(): void
    {
        $factory = new HttpExceptionFactory();

        $requestFailed = $this->requestFailed();
        $requestFailed['body'] = \str_repeat('a', HttpExceptionFactory::BODY_LIMIT + 500);

        $exception = $factory->create(400, self::MESSAGE_ID.'[HttpRequestLogger ERROR CODE] x', self::MESSAGE_ID, $requestFailed);
        \restore_exception_handler();

        self::assertInstanceOf(HttpResponseExceptionInterface::class, $exception);
        self::assertStringEndsWith(\str_repeat('a', HttpExceptionFactory::BODY_LIMIT).'... [truncated]', $exception->getMessage());
        self::assertSame($requestFailed, $exception->getRequestFailed());
    }

    public function testHttpExceptionMessageWithoutRequestDetailsStaysUnchanged(): void
    {
        $factory = new HttpExceptionFactory();

        $exception = $factory->create(404, 'Message ID=abc123 : fallback-message', self::MESSAGE_ID);
        \restore_exception_handler();

        self::assertInstanceOf(HttpNotFoundException::class, $exception);
        self::assertSame('Message ID=abc123 : fallback-message', $exception->getMessage());
        self::assertSame([], $exception->getRequestFailed());
    }

    public function testResponseBodyFormatterReturnsOriginalContentForInvalidJson(): void
    {
        $formatter = new ResponseBodyFormatter();
        $invalidJson = '{"key": "value"';

        $result = $formatter->format($invalidJson, ['password' => 'password']);
        \restore_exception_handler();

        self::assertSame($invalidJson, $result);
    }

    /**
     * @return array{method: string, url: string, code: int, body: string}
     */
    private function requestFailed(): array
    {
        return [
            'method' => 'GET',
            'url' => 'https://example.com/api',
            'code' => 400,
            'body' => '{"error": "invalid token"}',
        ];
    }
}
