<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\PhpUnit;

use ZJKiza\HttpResponseValidator\Validator\ArrayStructureExactValidation;
use ZJKiza\HttpResponseValidator\Validator\ArrayStructureInternalValidation;
use ZJKiza\HttpResponseValidator\Validator\Helper\ErrorCollector;

/**
 * @phpstan-ignore-next-line
 */
trait ArrayMatchesTrait
{
    /**
     * Partial match (allows for redundant keys).
     *
     * @param mixed[] $actual
     * @param mixed[] $expected
     */
    public function assertArrayStructureAndValues(array $actual, array $expected): void
    {
        PhpUnitTool::compare($actual, $expected, 'root', false);
        $this->addToAssertionCount(1);
    }

    /**
     * Strict match (1:1).
     *
     * @param mixed[] $actual
     * @param mixed[] $expected
     */
    public function assertArrayStrictStructureAndValues(array $actual, array $expected): void
    {
        PhpUnitTool::compare($actual, $expected, 'root', true);
        $this->addToAssertionCount(1);
    }

    /**
     * @param mixed[] $actual
     * @param mixed[] $expected
     */
    public function assertArrayStructure(array $actual, array $expected, bool $checkTypes = false, bool $ignoreNulls = true): void
    {
        $validator = new ArrayStructureInternalValidation(new ErrorCollector(), $ignoreNulls, $checkTypes);
        $validator->validate($expected, $actual);

        $errors = $validator->getErrorCollector()->all();

        if (true === (bool) $errors) {
            self::assertEmpty($errors, \implode(\PHP_EOL, $errors));

            return;
        }

        $this->addToAssertionCount(1);
    }

    /**
     * @param mixed[] $actual
     * @param mixed[] $expected
     */
    public function assertArrayStructureExact(array $actual, array $expected, bool $checkTypes = false, bool $ignoreNulls = true): void
    {
        $validator = new ArrayStructureExactValidation(new ErrorCollector(), $ignoreNulls, $checkTypes);
        $validator->validate($expected, $actual);

        $errors = $validator->getErrorCollector()->all();

        if (true === (bool) $errors) {
            self::assertEmpty($errors, \implode(\PHP_EOL, $errors));

            return;
        }

        $this->addToAssertionCount(1);
    }
}
