<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use ZJKiza\HttpResponseValidator\Enum\TypeCheck;
use ZJKiza\HttpResponseValidator\Exception\InvalidArgumentException;
use ZJKiza\HttpResponseValidator\Tests\Resources\KernelTestCase;
use ZJKiza\HttpResponseValidator\Validator\Helper\TypeChecker;
use ZJKiza\HttpResponseValidator\Validator\Type\ExpectedTypes;

final class TypeCheckerTest extends KernelTestCase
{
    #[DataProvider('provideIsValidCases')]
    public function testIsValid(string|TypeCheck|\ZJKiza\HttpResponseValidator\Contract\ExpectedTypeInterface $expectedType, mixed $value, bool $expected): void
    {
        self::assertSame($expected, TypeChecker::isValid($expectedType, $value));
        \restore_exception_handler();
    }

    public static function provideIsValidCases(): \Generator
    {
        yield 'Enum type valid' => [TypeCheck::INT, 12, true];
        yield 'Enum type invalid' => [TypeCheck::INT, '12', false];

        yield 'ArrayOfExpectedType valid' => [ExpectedTypes::arrayOf(TypeCheck::STRING), ['a', 'b'], true];
        yield 'ArrayOfExpectedType invalid item' => [ExpectedTypes::arrayOf(TypeCheck::STRING), ['a', 2], false];
        yield 'ArrayOfExpectedType invalid root type' => [ExpectedTypes::arrayOf(TypeCheck::STRING), 'not-array', false];

        yield 'UnionExpectedType valid first option' => [ExpectedTypes::union(TypeCheck::INT, TypeCheck::STRING), 7, true];
        yield 'UnionExpectedType valid second option' => [ExpectedTypes::union(TypeCheck::INT, TypeCheck::STRING), '7', true];
        yield 'UnionExpectedType invalid' => [ExpectedTypes::union(TypeCheck::INT, TypeCheck::STRING), false, false];

        yield 'Legacy array-of valid' => ['float[]', [1.2, 2.3], true];
        yield 'Legacy array-of invalid element' => ['float[]', [1.2, 2], false];
        yield 'Legacy array-of invalid root type' => ['float[]', 'nope', false];

        yield 'Legacy scalar valid' => ['string', 'ok', true];
        yield 'Legacy scalar invalid' => ['string', 123, false];
    }

    public function testThrowsForUnsupportedLegacyType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported type "uuid" in expected type "uuid".');

        TypeChecker::isValid('uuid', 'abc');
    }
}
