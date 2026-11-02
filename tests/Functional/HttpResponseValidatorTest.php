<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Tests\Functional;

use PHPUnit\Framework\Constraint\RegularExpression;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use ZJKiza\HttpResponseValidator\Contract\HandlerFactoryInterface;
use ZJKiza\HttpResponseValidator\Enum\TypeCheck;
use ZJKiza\HttpResponseValidator\Exception\HttpBadRequestException;
use ZJKiza\HttpResponseValidator\Exception\HttpNotFoundException;
use ZJKiza\HttpResponseValidator\Exception\InvalidArgumentException;
use ZJKiza\HttpResponseValidator\Exception\RuntimeException;
use ZJKiza\HttpResponseValidator\Handler\ArrayStructureValidateExactHandler;
use ZJKiza\HttpResponseValidator\Handler\ArrayStructureValidateInternalHandler;
use ZJKiza\HttpResponseValidator\Handler\ExtractResponseJsonHandler;
use ZJKiza\HttpResponseValidator\Handler\HttpResponseLoggerHandler;
use ZJKiza\HttpResponseValidator\Monad\Result;
use ZJKiza\HttpResponseValidator\PhpUnit\ArrayMatchesTrait;
use ZJKiza\HttpResponseValidator\Tests\Resources\KernelTestCase;
use ZJKiza\HttpResponseValidator\Tests\Resources\Logger\TestLogger;

final class HttpResponseValidatorTest extends KernelTestCase
{
    use ArrayMatchesTrait;

    private HandlerFactoryInterface $handlerFactory;

    protected function setUp(): void
    {
        parent::setUp();

        /** @psalm-suppress PropertyTypeCoercion */
        $this->handlerFactory = $this->getContainer()->get(HandlerFactoryInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset(
            $this->handlerFactory,
        );
    }

    public function testSuccessful(): void
    {
        $data = [
            'name' => 'Foo',
            'email' => 'foo@test.com',
            'password' => 'password123',
            'tokenTTL' => 'password123',
        ];

        $mockResponse = new MockResponse(
            \json_encode($data, JSON_THROW_ON_ERROR),
            [
                'response_headers' => [
                    'content-type' => 'application/json',
                ],
                'http_code' => 201,
            ]
        );

        $client = new MockHttpClient($mockResponse);
        $response = $client->request('GET', 'https://example.com');
        \restore_exception_handler();

        $result = Result::success($response)
            ->bind($this->handlerFactory->create(HttpResponseLoggerHandler::class)->setExpectedStatus(201)->addSensitiveKeys(['tokenTTL']))
            ->bind($this->handlerFactory->create(ExtractResponseJsonHandler::class)->setAssociative(true))
            ->bind($this->handlerFactory->create(ArrayStructureValidateInternalHandler::class)->setKeys(['name', 'email', 'password', 'tokenTTL']))
            ->getOrThrow();

        $expected = [
            'name' => 'Foo',
            'email' => 'foo@test.com',
            'password' => 'password123',
            'tokenTTL' => 'password123',
        ];

        $this->assertSame($expected, $result);
    }

    public function testExpectExceptionForUnexpectedStatusCode(): void
    {
        $data = [
            'name' => 'Foo',
            'email' => 'foo@test.com',
            'password' => 'password123',
            'tokenTTL' => 'password123',
        ];

        $mockResponse = new MockResponse(
            \json_encode($data, JSON_THROW_ON_ERROR),
            [
                'response_headers' => [
                    'content-type' => 'application/json',
                ],
                'http_code' => 200,
            ]
        );

        /** @var TestLogger $logger */
        $logger = $this->getContainer()->get(TestLogger::class);

        $client = new MockHttpClient($mockResponse);
        $response = $client->request('GET', 'https://example.com');
        \restore_exception_handler();

        $result = Result::success($response)
            ->bind(
                $this->handlerFactory
                ->create(HttpResponseLoggerHandler::class)
                    ->setExpectedStatus(201)
                    ->addSensitiveKeys(
                        ['tokenTTL']
                    )
            );

        $this->expectException(RuntimeException::class);

        $expected = [
            [
                'level' => 'error',
                'message' => function (string $message): void {
                    $pattern = '/Message ID=[a-f0-9]+ : .*Unexpected status code 200 expected 201/';
                    self::assertThat($message, new RegularExpression($pattern));
                },
                'context' => [
                    'http_request_failed' => [
                        'method' => 'GET',
                        'url' => 'https://example.com/',
                        'body' => function (string $value): void {

                            $expected = [
                                'name' => 'Foo',
                                'email' => 'foo@test.com',
                                'password' => '***',
                                'tokenTTL' => '***',
                            ];

                            $this->assertSame($expected, \json_decode($value, true));
                        },
                    ],
                ],
            ],
            [
                'level' => 'error',
                'message' => function (string $message): void {
                    $pattern = '/Message ID=[a-f0-9]+ : .*\\[HttpRequestLogger ERROR CODE\\] Unexpected status code 200 expected 201/';
                    self::assertThat($message, new RegularExpression($pattern));
                },
                'context' => [
                    'trace' => function (mixed $trace): void {
                        $this->assertIsArray($trace);
                    },
                ],
            ],
        ];

        $this->assertArrayStructureAndValues($logger->records, $expected);

        $result->getOrThrow();
    }

    public function testBadRequestExceptionContainsLoggedMessageWithSingleMessageId(): void
    {
        /** @var TestLogger $logger */
        $logger = $this->getContainer()->get(TestLogger::class);

        $mockResponse = new MockResponse(
            '{"error":"invalid","errors":["first","second"],"token":"secret-body-token"}',
            [
                'response_headers' => [
                    'content-type' => 'application/json',
                ],
                'http_code' => 400,
            ]
        );

        $client = new MockHttpClient($mockResponse);
        $response = $client->request('GET', 'https://example.com/auth/secret-path-token?token=secret-query-token&lang=en');
        \restore_exception_handler();

        $result = Result::success($response)
            ->bind($this->handlerFactory->create(HttpResponseLoggerHandler::class)->addSensitiveValues(['secret-path-token']));

        try {
            $result->getOrThrow();
            self::fail('Expected exception was not thrown.');
        } catch (HttpBadRequestException $exception) {
            /** @var array<int, array{level: string, message: string, context: array<string, mixed>}> $records */
            $records = $logger->records;

            // The logger writes the message first, the handler writes it again with the trace.
            self::assertCount(2, $records);

            // One identifier is used everywhere: in the log, in the exception message and in the property.
            self::assertMatchesRegularExpression('/^Message ID=[a-f0-9]+$/', $exception->getMessageId());
            self::assertSame(1, \substr_count($exception->getMessage(), 'Message ID='));
            self::assertTrue(\str_starts_with($exception->getMessage(), $exception->getMessageId()));
            self::assertStringContainsString($exception->getMessageId(), $records[0]['message']);
            self::assertStringContainsString($exception->getMessageId(), $records[1]['message']);

            // The exception message carries what is logged, so the cause is visible without the logs.
            self::assertStringContainsString('[HttpRequestLogger ERROR CODE] Unexpected status code 400 expected 200', $exception->getMessage());
            self::assertStringContainsString('GET https://example.com/auth/***?token=***&lang=en', $exception->getMessage());
            self::assertStringContainsString('"error": "invalid"', $exception->getMessage());

            // The body must not be touched by the "run place" substitution (": [" inside the body).
            $details = \explode(' | ', $exception->getMessage(), 2)[1] ?? '';
            self::assertStringContainsString('"errors": [ "first", "second" ]', $details);
            self::assertStringNotContainsString('->', $details);

            // Sensitive data is masked everywhere.
            self::assertStringNotContainsString('secret-path-token', $exception->getMessage());
            self::assertStringNotContainsString('secret-query-token', $exception->getMessage());
            self::assertStringNotContainsString('secret-body-token', $exception->getMessage());
            self::assertStringNotContainsString('secret-path-token', (string) \json_encode($records[0]['context']));
            self::assertStringNotContainsString('secret-query-token', (string) \json_encode($records[0]['context']));

            self::assertSame(400, $exception->getRequestFailed()['code']);
            self::assertSame('https://example.com/auth/***?token=***&lang=en', $exception->getRequestFailed()['url']);
        }
    }

    public function testSensitiveValuesAreNotMaskedWhenNotSet(): void
    {
        $handler = $this->handlerFactory->create(HttpResponseLoggerHandler::class);
        $handler->addSensitiveValues(['secret-path-token']);
        $handler->addSensitiveValues();

        $client = new MockHttpClient(new MockResponse('{}', ['http_code' => 404]));
        $response = $client->request('GET', 'https://example.com/auth/secret-path-token');
        \restore_exception_handler();

        try {
            Result::success($response)->bind($handler)->getOrThrow();
            self::fail('Expected exception was not thrown.');
        } catch (HttpNotFoundException $exception) {
            self::assertStringContainsString('https://example.com/auth/secret-path-token', $exception->getMessage());
        }
    }

    public function testExpectExceptionForInvalidJsonFormat(): void
    {
        /** @var TestLogger $logger */
        $logger = $this->getContainer()->get(TestLogger::class);

        $jsonBody = '{ "name":"Foo", "email"=>"foo@examle.com" }';

        $mockResponse = new MockResponse(
            $jsonBody,
            [
                'response_headers' => [
                    'content-type' => 'application/json',
                ],
                'http_code' => 200,
            ]
        );

        $client = new MockHttpClient($mockResponse);
        $response = $client->request('GET', 'https://example.com');
        \restore_exception_handler();

        $result = Result::success($response)
            ->bind($this->handlerFactory->create(ExtractResponseJsonHandler::class)->setAssociative());

        $this->expectException(\Throwable::class);

        $expected = [
            [
                'level' => 'error',
                'message' => function (string $message): void {
                    $pattern = '/^[ZJKiza\\\\HttpResponseValidator\\\\Handler\\\\ExtractResponseJsonHandler\] Message ID=[a-f0-9]+ : PHPUnit\\\\Framework\\\\TestCase::(?:runTest|invokeTestMethod) -> \[ExtractResponseJsonHandler\] Syntax error$/';
                    self::assertThat($message, new RegularExpression($pattern));
                },
                'context' => function (mixed $trace): void {
                    $this->assertIsArray($trace);
                },

            ],
        ];

        $this->assertArrayStructureAndValues($logger->records, $expected);

        $result->getOrThrow();
    }

    public function testExpectExceptionForValidateArrayKeysWhereKeyNotExist(): void
    {
        $data = [
            'name' => 'Foo',
            'email' => 'foo@test.com',
        ];

        /** @var TestLogger $logger */
        $logger = $this->getContainer()->get(TestLogger::class);

        $result = Result::success($data)
            ->bind($this->handlerFactory->create(ArrayStructureValidateInternalHandler::class)->setKeys(['name', 'lorem']));
        \restore_exception_handler();

        $this->expectException(InvalidArgumentException::class);

        $expected = [
            [
                'level' => 'error',
                'message' => function (string $message): void {
                    $pattern = '/^\[ZJKiza\\\\HttpResponseValidator\\\\Handler\\\\ArrayStructureValidateInternalHandler\] Message ID=[a-f0-9]+ :  PHPUnit\\\\Framework\\\\TestCase::(?:runTest|invokeTestMethod) -> \[ArrayStructureValidateInternalHandler\] Errors: Missing required key "root\.lorem"\.$/';
                    self::assertThat($message, new RegularExpression($pattern));
                },
                'context' => function (mixed $trace): void {
                    $this->assertIsArray($trace);
                },

            ],
        ];

        $this->assertArrayStructureAndValues($logger->records, $expected);

        $result->getOrThrow();
    }

    public function testExpectExceptionForArrayStructureValidateInternalWhereKeyNotExistWithMorKeys(): void
    {
        $data = [
            'name' => 'Foo',
            'email' => 'foo@test.com',
        ];

        /** @var TestLogger $logger */
        $logger = $this->getContainer()->get(TestLogger::class);

        $result = Result::success($data)
            ->bind($this->handlerFactory->create(ArrayStructureValidateInternalHandler::class)->setKeys(['name', 'lorem', 'bar']));
        \restore_exception_handler();

        $this->expectException(InvalidArgumentException::class);

        $expected = [
            [
                'level' => 'error',
                'message' => function (string $message): void {
                    $pattern = '/^\[ZJKiza\\\\HttpResponseValidator\\\\Handler\\\\ArrayStructureValidateInternalHandler\] Message ID=[a-f0-9]+ :  PHPUnit\\\\Framework\\\\TestCase::(?:runTest|invokeTestMethod) -> \[ArrayStructureValidateInternalHandler\] Errors: Missing required key "root\.lorem", Missing required key "root\.bar"\.$/';
                    self::assertThat($message, new RegularExpression($pattern));
                },
                'context' => function (mixed $trace): void {
                    $this->assertIsArray($trace);
                },

            ],
        ];

        $this->assertArrayStructureAndValues($logger->records, $expected);

        $result->getOrThrow();
    }

    public function testExpectExceptionForArrayStructureValidateExactWhereKeyNotExistWithMorKeys(): void
    {
        $data = [
            'args' => [
                'test' => '123',
            ],
            'headers' => [
                'host' => 'postman-echo.com',
                'dnt' => '1',
                'foo' => '1',
                'bar' => [
                    'barKey1' => 'lorem',
                    'barKey2' => 1,
                ],
            ],
        ];

        $structure = [
            'args' => [
                'test' => TypeCheck::STRING,
            ],
            'headers' => [
                'host' => TypeCheck::STRING,
                'dnt' => TypeCheck::STRING,
                'foo' => TypeCheck::STRING,
                'bar' => [
                    'barKey1' => TypeCheck::STRING,
                ],
            ],
        ];

        /** @var TestLogger $logger */
        $logger = $this->getContainer()->get(TestLogger::class);

        $result = Result::success($data)
            ->bind($this->handlerFactory->create(ArrayStructureValidateExactHandler::class)->setKeys($structure));
        \restore_exception_handler();

        $this->expectException(InvalidArgumentException::class);

        $expected = [
            [
                'level' => 'error',
                'message' => function (string $message): void {
                    $pattern = '/^\[ZJKiza\\\\HttpResponseValidator\\\\Handler\\\\ArrayStructureValidateExactHandler\] Message ID=[a-f0-9]+ ?: +PHPUnit\\\\Framework\\\\TestCase::(?:runTest|invokeTestMethod) -> \[ArrayStructureValidateExactHandler\] Errors: Exact key mismatch at \"root\.headers\.bar\"\. Expected: PHPUnit\\\\Framework\\\\TestCase::(?:runTest|invokeTestMethod) -> \[\"barKey1\"\], got: PHPUnit\\\\Framework\\\\TestCase::(?:runTest|invokeTestMethod) -> \[\"barKey1\",\"barKey2\"\]\.$/';
                    self::assertThat($message, new RegularExpression($pattern));
                },
                'context' => function (mixed $trace): void {
                    $this->assertIsArray($trace);
                },

            ],
        ];

        $this->assertArrayStructureAndValues($logger->records, $expected);

        $result->getOrThrow();
    }
}
