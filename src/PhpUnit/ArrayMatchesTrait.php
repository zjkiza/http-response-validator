<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\PhpUnit;

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
}
