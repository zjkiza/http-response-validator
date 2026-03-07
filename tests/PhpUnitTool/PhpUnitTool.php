<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Tests\PhpUnitTool;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

final class PhpUnitTool extends TestCase
{
    /**
     * @param array $actual
     * @param array $expected
     */
    public static function assertArrayStructure(array $actual, array $expected): void
    {
        self::compare($actual, $expected, 'root');
    }

    /**
     * @param mixed $actual
     * @param mixed $expected
     */
    private static function compare(mixed $actual, mixed $expected, string $path): void
    {
        if (\is_callable($expected)) {
            try {
                $expected($actual);
            } catch (AssertionFailedError $e) {
                self::fail("Assertion failed at {$path}: ".$e->getMessage());
            }
            return;
        }

        if (\is_array($expected)) {

            if (!\is_array($actual)) {
                self::fail("Type mismatch at {$path}. Expected array, got ".\gettype($actual));
            }

            /**
             * LISTA
             */
            if (\array_is_list($expected)) {

                self::assertCount(
                    \count($expected),
                    $actual,
                    "List size mismatch at {$path}"
                );

                foreach ($expected as $i => $expItem) {
                    $newPath = "{$path}[{$i}]";

                    if (!\array_key_exists($i, $actual)) {
                        self::fail("Missing index {$i} at {$path}");
                    }

                    self::compare($actual[$i], $expItem, $newPath);
                }

                return;
            }

            /**
             * ASSOCIATIVE ARRAY
             */
            foreach ($expected as $key => $expValue) {

                $newPath = "{$path}.{$key}";

                if (!\array_key_exists($key, $actual)) {
                    self::fail("Missing key '{$key}' at {$path}");
                }

                self::compare($actual[$key], $expValue, $newPath);
            }

            return;
        }

        if ($actual !== $expected) {
            self::fail(
                "Value mismatch at {$path}\n".
                "Expected: ".\var_export($expected, true)."\n".
                "Actual: ".\var_export($actual, true)
            );
        }
    }
}
