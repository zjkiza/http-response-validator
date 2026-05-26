<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Tests\Functional;

use PHPUnit\Framework\AssertionFailedError;
use ZJKiza\HttpResponseValidator\Contract\HandlerFactoryInterface;
use ZJKiza\HttpResponseValidator\Enum\TypeCheck;
use ZJKiza\HttpResponseValidator\Handler\ArrayStructureValidateExactHandler;
use ZJKiza\HttpResponseValidator\Handler\ArrayStructureValidateInternalHandler;
use ZJKiza\HttpResponseValidator\Monad\Result;
use ZJKiza\HttpResponseValidator\PhpUnit\ArrayMatchesTrait;
use ZJKiza\HttpResponseValidator\PhpUnit\PhpUnitTool;
use ZJKiza\HttpResponseValidator\Tests\Resources\KernelTestCase;
use ZJKiza\HttpResponseValidator\Validator\ArrayStructureInternalValidation;
use ZJKiza\HttpResponseValidator\Validator\Helper\ErrorCollector;
use ZJKiza\HttpResponseValidator\Validator\Type\ArrayOfExpectedType;
use ZJKiza\HttpResponseValidator\Validator\Type\ExpectedTypes;

final class CoverageGapsTest extends KernelTestCase
{
    use ArrayMatchesTrait;

    private HandlerFactoryInterface $handlerFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handlerFactory = $this->getContainer()->get(HandlerFactoryInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->handlerFactory);
    }

    public function testFailureBindIsShortCircuitedAndGetValueReturnsOriginalValue(): void
    {
        $result = Result::failure('initial error');
        \restore_exception_handler();

        $bound = $result->bind(static fn (): Result => Result::success('should not execute'));

        self::assertSame($result, $bound);
        self::assertSame('initial error', $result->getValue());
        self::assertSame('initial error', $result->getOrThrow());
    }

    public function testArrayOfExpectedTypeDescribeReturnsArrayNotation(): void
    {
        \restore_exception_handler();

        $expectedType = new ArrayOfExpectedType(TypeCheck::STRING);

        self::assertSame('string[]', $expectedType->describe());
    }

    public function testArrayStructureExactHandlerSettersAffectValidationFlow(): void
    {
        $data = [
            'user' => [
                'id' => 10,
                'optional' => null,
            ],
        ];

        $structure = [
            'user' => [
                'id' => TypeCheck::INT,
                'optional' => ExpectedTypes::union(TypeCheck::STRING, TypeCheck::NULL),
            ],
        ];

        $handler = $this->handlerFactory
            ->create(ArrayStructureValidateExactHandler::class)
            ->setKeys($structure)
            ->setIgnoreNulls(true)
            ->setCheckTypes(true);

        $validated = Result::success($data)
            ->bind($handler)
            ->getOrThrow();
        \restore_exception_handler();

        self::assertSame($data, $validated);
    }

    public function testArrayStructureInternalHandlerSettersAffectValidationFlow(): void
    {
        $data = [
            'user' => [
                'id' => 10,
                'optional' => null,
            ],
        ];

        $structure = [
            'user' => [
                'id' => TypeCheck::INT,
                'optional' => ExpectedTypes::union(TypeCheck::STRING, TypeCheck::NULL),
            ],
        ];

        $handler = $this->handlerFactory
            ->create(ArrayStructureValidateInternalHandler::class)
            ->setKeys($structure)
            ->setIgnoreNulls(true)
            ->setCheckTypes(true);

        $validated = Result::success($data)
            ->bind($handler)
            ->getOrThrow();
        \restore_exception_handler();

        self::assertSame($data, $validated);
    }

    public function testArrayStructureExactTraitMethodPassesForMatchingStructure(): void
    {
        \restore_exception_handler();

        $actual = [
            'payload' => [
                'items' => [
                    [
                        'id' => 1,
                        'name' => 'foo',
                    ],
                ],
            ],
        ];

        $expected = [
            'payload' => [
                'items' => [
                    '*' => [
                        'id' => TypeCheck::INT,
                        'name' => TypeCheck::STRING,
                    ],
                ],
            ],
        ];

        $this->assertArrayStructureExact($actual, $expected, true);
    }

    public function testPhpUnitToolStrictCompareFailsOnExtraKeys(): void
    {
        \restore_exception_handler();

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Unexpected keys at root: extra');

        PhpUnitTool::compare(
            ['name' => 'Foo', 'extra' => 'value'],
            ['name' => 'Foo'],
            'root',
            true
        );
    }

    public function testSuccessBindConvertsThrowableToFailure(): void
    {
        $result = Result::success(['id' => 1])
            ->bind(static function (): Result {
                throw new \RuntimeException('bind failure');
            });
        \restore_exception_handler();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('bind failure');

        $result->getOrThrow();
    }

    public function testInternalValidationSupportsLegacyUnionStringTypes(): void
    {
        $validator = new ArrayStructureInternalValidation(new ErrorCollector(), false, true);

        $validator->validate(
            ['meta' => ['status' => 'int|bool']],
            ['meta' => ['status' => 'bad-type']]
        );
        \restore_exception_handler();

        self::assertSame(
            ['Key "root.meta.status" expects type "int|bool", got "string"'],
            $validator->getErrorCollector()->all()
        );
    }

    public function testInternalValidationSupportsLegacyArrayOfStringTypes(): void
    {
        $validator = new ArrayStructureInternalValidation(new ErrorCollector(), false, true);

        $validator->validate(
            ['meta' => ['scores' => 'float[]']],
            ['meta' => ['scores' => [1.0, 2]]]
        );
        \restore_exception_handler();

        self::assertSame(
            ['Key "root.meta.scores[1]" expects type "float", got "integer"'],
            $validator->getErrorCollector()->all()
        );
    }
}
