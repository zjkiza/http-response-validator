<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use ZJKiza\HttpResponseValidator\Exception\InvalidArgumentException;
use ZJKiza\HttpResponseValidator\Tests\Resources\KernelTestCase;
use ZJKiza\HttpResponseValidator\Validator\ArrayStructureInternalValidation;
use ZJKiza\HttpResponseValidator\Validator\Helper\ErrorCollector;

final class ArrayStructureInternalValidatorTest extends KernelTestCase
{
    #[DataProvider('provideSuccessfulValidationCases')]
    public function testSuccessful(array $structure, array $data, bool $ignoreNulls, bool $checkTypes): void
    {
        $validator = new ArrayStructureInternalValidation(new ErrorCollector(), $ignoreNulls, $checkTypes);

        $validator->validate($structure, $data);

        $this->assertFalse($validator->getErrorCollector()->hasErrors());
    }

    public static function provideSuccessfulValidationCases(): \Generator
    {
        yield 'No type check, only key check' => [
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
            false,
            false,
        ];

        yield 'Type and key verification' => [
            [
                'args' => [
                    'test' => 'string',
                ],
                'headers' => [
                    'host' => 'string',
                    'dnt' => 'string|float',
                    'foo' => true,
                    'ad' => [
                        'bb' => 'array',
                        'cc' => 'object',
                        'dd' => 'null',
                    ],
                ],
                'body' => [
                    'items' => [
                        '*' => [
                            'name' => 'string',
                            'age' => 'int',
                        ],
                    ],
                    'errors' => 'string[]',
                ],
            ],
            [
                'args' => [
                    'test' => '123',
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
                        'error 2',
                        'error 3',
                    ],
                ],
            ],
            true,
            true,
        ];
    }

    #[DataProvider('provideErrorValidationCases')]
    public function testExpectErrors(array $structure, array $data, array $expectedErrors, bool $ignoreNulls, bool $checkTypes): void
    {
        $validator = new ArrayStructureInternalValidation(new ErrorCollector(), $ignoreNulls, $checkTypes);

        $validator->validate($structure, $data);

        $this->assertSame($expectedErrors, $validator->getErrorCollector()->all());
    }

    public static function provideErrorValidationCases(): \Generator
    {
        yield 'Withe expected types and ignore nulls set to false' => [
            [
                'args' => [
                    'test' => 'string|bool|null',
                ],
                'items' => 'array|null',
            ],
            [
                'args' => [
                    'test' => '123',
                ],
                'items' => null,
            ],
            [
                'Key "root.items" cannot be null',
            ],
            false,
            true,
        ];

        yield 'When validation type is incorrected' => [
            [
                'args' => [
                    'test_string' => 'string',
                    'test_int' => 'int',
                    'test_float' => 'float',
                    'test_bool' => 'bool',
                    'test_array' => 'array',
                    'test_object' => 'object',
                    'test_null' => 'null',
                ],
            ],
            [
                'args' => [
                    'test_string' => 123,
                    'test_int' => '123',
                    'test_float' => '123',
                    'test_bool' => '123',
                    'test_array' => '123',
                    'test_object' => '123',
                    'test_null' => '123',
                ],
            ],
            [
                'Key "root.args.test_string" expects type "string", got "integer"',
                'Key "root.args.test_int" expects type "int", got "string"',
                'Key "root.args.test_float" expects type "float", got "string"',
                'Key "root.args.test_bool" expects type "bool", got "string"',
                'Key "root.args.test_array" expects type "array", got "string"',
                'Key "root.args.test_object" expects type "object", got "string"',
                'Key "root.args.test_null" expects type "null", got "string"',
            ],
            false,
            true,
        ];

        yield 'With check type, missing key and wrong type' => [
            [
                'headers' => [
                    'host' => 'string',
                    'bar' => 'string',
                    'foo' => true,
                    'ad' => [
                        'bb' => 'array',
                        'cc' => 'string',
                        'ee' => 'string[]',
                    ],
                ],
            ],
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
                            'ipsum',
                            22,
                        ],
                    ],
                ],
            ],
            [
                'Missing required key "root.headers.bar"',
                'Key "root.headers.ad.cc" expects type "string", got "object"',
                'Key "root.headers.ad.ee[2]" expects type "string", got "integer"',
            ],
            true,
            true,
        ];

        yield 'With check types (union types, array value) witch is not correct' => [
            [
                'args' => [
                    'test' => 'int|bool|null',
                ],
                'numbers' => 'float[]',
            ],
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
                'Key "root.args.test" expects type "int|bool|null", got "string"',
                'Key "root.numbers[2]" expects type "float", got "integer"',
            ],
            true,
            true,
        ];
    }

    #[DataProvider('provideInvalidExpectedTypes')]
    public function testExpectExceptionWithCheckTypesWitchIsCorrect(array $structure, array $data): void
    {
        $this->expectException(InvalidArgumentException::class);

        $validator = new ArrayStructureInternalValidation(new ErrorCollector(), true, true);

        $validator->validate($structure, $data);
    }

    public static function provideInvalidExpectedTypes(): \Generator
    {
        yield 'Array value check type' => [
            ['items' => 'string|int[]'],
            ['items' => [1, 2, 3]],
        ];

        yield 'Unsupported type in expected type' => [
            ['items' => 'unsupported_type'],
            ['items' => 'value'],
        ];
    }
}
