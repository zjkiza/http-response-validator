<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use ZJKiza\HttpResponseValidator\Exception\RuntimeException;
use ZJKiza\HttpResponseValidator\HttpLogger\HttpExceptionFactory;
use ZJKiza\HttpResponseValidator\HttpLogger\ResponseBodyFormatter;
use ZJKiza\HttpResponseValidator\Tests\Resources\KernelTestCase;

final class HttpLoggerComponentsTest extends KernelTestCase
{
    #[DataProvider('provideExceptionFactoryCases')]
    public function testHttpExceptionFactoryCreate(int $statusCode, string $expectedClass, string $expectedMessagePart): void
    {
        $factory = new HttpExceptionFactory();

        $exception = $factory->create($statusCode, 'fallback-message', 'Message ID=abc123 :');
        \restore_exception_handler();

        self::assertInstanceOf($expectedClass, $exception);
        self::assertStringContainsString($expectedMessagePart, $exception->getMessage());
    }

    public static function provideExceptionFactoryCases(): \Generator
    {
        yield '400 bad request' => [400, BadRequestHttpException::class, 'Bad request.'];
        yield '401 unauthorized' => [401, UnauthorizedHttpException::class, 'Invalid credentials'];
        yield '403 forbidden' => [403, AccessDeniedHttpException::class, 'Access denied.'];
        yield '404 not found' => [404, NotFoundHttpException::class, 'Not found.'];
        yield '500 fallback runtime exception' => [500, RuntimeException::class, 'fallback-message'];
    }

    public function testResponseBodyFormatterReturnsOriginalContentForInvalidJson(): void
    {
        $formatter = new ResponseBodyFormatter();
        $invalidJson = '{"key": "value"';

        $result = $formatter->format($invalidJson, ['password' => 'password']);
        \restore_exception_handler();

        self::assertSame($invalidJson, $result);
    }
}
