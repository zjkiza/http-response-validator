<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Tests\Functional;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use ZJKiza\HttpResponseValidator\Enum\TypeCheck;
use ZJKiza\HttpResponseValidator\PhpUnit\ArrayMatchesTrait;
use ZJKiza\HttpResponseValidator\Tests\Resources\KernelTestCase;
use ZJKiza\HttpResponseValidator\Validator\Type\ExpectedTypes;

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

    #[DataProvider('getDataForSuccessfulArrayStructureAndValues')]
    public function testSuccessfulArrayStructureAndValues(array $actual, array $expected): void
    {
        $this->assertArrayStructureAndValues($actual, $expected);
    }

    public static function getDataForSuccessfulArrayStructureAndValues(): \Generator
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

    #[DataProvider('getDataForSuccessfulArrayStructure')]
    public function testSuccessfulArrayStructure(array $items, array $expected, bool $checkTypes): void
    {
        $this->assertArrayStructure($items, $expected, $checkTypes);
    }

    public static function getDataForSuccessfulArrayStructure(): \Generator
    {
        yield 'No type check, only key check' => [
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
                    'test',
                ],
                'headers' => [
                    'host',
                    'dnt',
                    'foo',
                    'ad' => [
                        'bb',
                        'cc',
                    ],
                ],
                'body' => [
                    'items' => [
                        '*' => [
                            'name',
                            'age',
                        ],
                    ],
                ],
            ],
            false,
        ];

        yield 'Type and key verification' => [
            [
                'args' => [
                    'test' => '123',
                    'empty-string' => '',
                    'filled-string' => 'filled',
                ],
                'headers' => [
                    'host' => 'postman-echo.com',
                    'dnt' => 1.23,
                    'foo' => 'bool',
                    'ad' => [
                        'bb' => [],
                        'cc' => new class () {
                        },
                        'dd' => null,
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
                    'errors' => [
                        'error 1',
                        '',
                        'error 3',
                    ],
                    'errors-not-empty-string' => [
                        'error 1',
                        'error 2',
                        'error 3',
                    ],
                ],
            ],
            [
                'args' => [
                    'test' => TypeCheck::STRING,
                    'empty-string' => TypeCheck::STRING,
                    'filled-string' => TypeCheck::NON_EMPTY_STRING,
                ],
                'headers' => [
                    'host' => TypeCheck::STRING,
                    'dnt' => ExpectedTypes::union(TypeCheck::STRING, TypeCheck::FLOAT),
                    'foo' => true,
                    'ad' => [
                        'bb' => TypeCheck::ARRAY,
                        'cc' => TypeCheck::OBJECT,
                        'dd' => TypeCheck::NULL,
                    ],
                ],
                'body' => [
                    'items' => [
                        '*' => [
                            'name' => TypeCheck::STRING,
                            'age' => TypeCheck::INT,
                        ],
                    ],
                    'errors' => ExpectedTypes::arrayOf(TypeCheck::STRING),
                    'errors-not-empty-string' => ExpectedTypes::arrayOf(TypeCheck::NON_EMPTY_STRING),
                ],
            ],
            true,
        ];
    }

    #[DataProvider('getDataForExpectedErrorsArrayStructure')]
    public function testExpectedErrorsArrayStructure(array $items, array $expected, string $message, bool $checkTypes): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage($message);

        $this->assertArrayStructure($items, $expected, $checkTypes);
    }

    public static function getDataForExpectedErrorsArrayStructure(): \Generator
    {
        yield 'With check type, missing key and wrong type' => [
            [
                'headers' => [
                    'host' => 'postman-echo.com',
                    'dnt' => 1.23,
                    'foo' => 'bool',
                    'ad' => [
                        'bb' => [],
                        'cc' => new class () {
                        },
                        'dd' => null,
                        'ee' => [
                            'lorem',
                            '',
                            22,
                        ],
                        'ff' => [
                            'foo',
                            '',
                            'bar',
                        ],
                    ],
                ],
            ],
            [
                'headers' => [
                    'host' => TypeCheck::STRING,
                    'bar' => TypeCheck::STRING,
                    'foo' => true,
                    'ad' => [
                        'bb' => TypeCheck::ARRAY,
                        'cc' => TypeCheck::STRING,
                        'ee' => ExpectedTypes::arrayOf(TypeCheck::STRING),
                        'ff' => ExpectedTypes::arrayOf(TypeCheck::NON_EMPTY_STRING),
                    ],
                ],
            ],
            'Missing required key "root.headers.bar"'.PHP_EOL.
            'Key "root.headers.ad.cc" expects type "string", got "object"'.PHP_EOL.
            'Key "root.headers.ad.ee[2]" expects type "string", got "integer"'.PHP_EOL.
            'Key "root.headers.ad.ff[1]" expects type "non-empty-string", got "string"'.PHP_EOL.
            'Failed asserting that an array is empty.',
            true,
        ];

        yield 'When validation type is incorrected' => [
            [
                'args' => [
                    'test_string' => 123,
                    'test_non_empty_string' => '',
                    'test_int' => '123',
                    'test_float' => '123',
                    'test_bool' => '123',
                    'test_array' => '123',
                    'test_object' => '123',
                    'test_null' => '123',
                ],
            ],
            [
                'args' => [
                    'test_string' => TypeCheck::STRING,
                    'test_non_empty_string' => TypeCheck::NON_EMPTY_STRING,
                    'test_int' => TypeCheck::INT,
                    'test_float' => TypeCheck::FLOAT,
                    'test_bool' => TypeCheck::BOOL,
                    'test_array' => TypeCheck::ARRAY,
                    'test_object' => TypeCheck::OBJECT,
                    'test_null' => TypeCheck::NULL,
                ],
            ],
            'Key "root.args.test_string" expects type "string", got "integer"'.PHP_EOL.
            'Key "root.args.test_non_empty_string" expects type "non-empty-string", got "string"'.PHP_EOL.
            'Key "root.args.test_int" expects type "int", got "string"'.PHP_EOL.
            'Key "root.args.test_float" expects type "float", got "string"'.PHP_EOL.
            'Key "root.args.test_bool" expects type "bool", got "string"'.PHP_EOL.
            'Key "root.args.test_array" expects type "array", got "string"'.PHP_EOL.
            'Key "root.args.test_object" expects type "object", got "string"'.PHP_EOL.
            'Key "root.args.test_null" expects type "null", got "string"'.PHP_EOL.
            'Failed asserting that an array is empty.',
            true,
        ];

        yield 'With check types (union types, array value) witch is not correct' => [
            [
                'args' => [
                    'test' => '123',
                ],
                'numbers' => [
                    12.56,
                    22.33,
                    44,
                ],
            ],
            [
                'args' => [
                    'test' => ExpectedTypes::union(TypeCheck::INT, TypeCheck::BOOL, TypeCheck::NULL),
                ],
                'numbers' => ExpectedTypes::arrayOf(TypeCheck::FLOAT),
            ],
            'Key "root.args.test" expects type "int|bool|null", got "string"'.PHP_EOL.
            'Key "root.numbers[2]" expects type "float", got "integer"'.PHP_EOL.
            'Failed asserting that an array is empty.',
            true,
        ];
    }
}
