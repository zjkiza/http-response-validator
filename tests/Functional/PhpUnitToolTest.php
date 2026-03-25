<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use ZJKiza\HttpResponseValidator\PhpUnit\ArrayMatchesTrait;
use ZJKiza\HttpResponseValidator\Tests\Resources\KernelTestCase;

final class PhpUnitToolTest extends KernelTestCase
{
    use ArrayMatchesTrait;

    #[DataProvider('getDataForSuccessfulStrictStrict')]
    public function testSuccessfulStrictStrict(array $actualExpected): void
    {
        $this->assertArrayStrictStructureAndValues($actualExpected, $actualExpected);
    }

    public static function getDataForSuccessfulStrictStrict(): \Generator
    {
        yield 'Associate array' => [
            [
                'name' => 'Foo',
                'email' => 'foo@test.com',
                'password' => 'password123',
                'tokenTTL' => 'password123',
            ],
        ];

        yield 'Associate array more complex structure' => [
            [
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
            ],
        ];

        yield 'Associate array more complex structure with array list' => [
            [
                'args' => [
                    'test' => '123',
                ],
                'headers' => [
                    'host' => 'postman-echo.com',
                    'dnt' => '1',
                    'foo' => '1',
                    'ad' => [
                        'bb' => 'lorem',
                        'cc' => 1,
                    ],
                ],
                'body' => [
                    'items' => [
                        [
                            'name' => 'name1',
                            'age' => 20,
                        ],
                        [
                            'name' => 'name2',
                            'age' => 22,
                        ],
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('getDataForSuccessfulStrict')]
    public function testSuccessfulStrict(array $actual, array $expected): void
    {
        $this->assertArrayStructureAndValues($actual, $expected);
    }

    public static function getDataForSuccessfulStrict(): \Generator
    {
        yield 'Associate array' => [
            [
                'name' => 'Foo',
                'email' => 'foo@test.com',
                'password' => 'password123',
                'tokenTTL' => 'password123',
            ],
            [
                'name' => 'Foo',
                'email' => 'foo@test.com',
                'tokenTTL' => 'password123',
            ],
        ];

        yield 'Associate array more complex structure' => [
            [
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
            ],
            [
                'args' => [
                    'test' => '123',
                ],
                'headers' => [
                    'host' => 'postman-echo.com',
                    'dnt' => '1',
                    'bar' => [
                        'barKey1' => 'lorem',
                        'barKey2' => 1,
                    ],
                ],
            ],
        ];

        yield 'Associate array more complex structure with array list' => [
            [
                'args' => [
                    'test' => '123',
                ],
                'headers' => [
                    'host' => 'postman-echo.com',
                    'dnt' => '1',
                    'foo' => '1',
                    'ad' => [
                        'bb' => 'lorem',
                        'cc' => 1,
                    ],
                ],
                'body' => [
                    'items' => [
                        [
                            'name' => 'name1',
                            'age' => 20,
                        ],
                        [
                            'name' => 'name2',
                            'age' => 22,
                        ],
                    ],
                ],
            ],
            [
                'args' => [
                    'test' => '123',
                ],
                'headers' => [
                    'host' => 'postman-echo.com',
                    'dnt' => '1',
                    'foo' => '1',
                    'ad' => [
                        'bb' => 'lorem',
                        'cc' => 1,
                    ],
                ],
                'body' => [
                    'items' => [
                        [
                            'name' => 'name1',
                        ],
                        [
                            'name' => 'name2',
                            'age' => 22,
                        ],
                    ],
                ],
            ],
        ];
    }
}
