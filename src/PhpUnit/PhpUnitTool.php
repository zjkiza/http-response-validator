<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\PhpUnit;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

final class PhpUnitTool extends TestCase
{
    public static function compare(
        mixed $actual,
        mixed $expected,
        string $path,
        bool $strict,
    ): void {

        if (\is_callable($expected)) {
            try {
                $expected($actual);
            } catch (AssertionFailedError $e) {
                self::fail(\sprintf('Assertion failed at %s: ', $path).$e->getMessage());
            }

            return;
        }

        if (\is_array($expected)) {

            if (!\is_array($actual)) {
                self::fail(\sprintf('Type mismatch at %s. Expected array, got ', $path).\gettype($actual));
            }

            if (\array_is_list($expected)) {

                if ($strict && !\array_is_list($actual)) {
                    self::fail(\sprintf('Type mismatch at %s. Expected list, got associative array', $path));
                }

                self::assertCount(
                    \count($expected),
                    $actual,
                    'List size mismatch at '.$path
                );

                foreach ($expected as $i => $expItem) {

                    if (!\array_key_exists($i, $actual)) {
                        self::fail(\sprintf('Missing index %d at %s', $i, $path));
                    }

                    self::compare(
                        $actual[$i],
                        $expItem,
                        \sprintf('%s[%d]', $path, $i),
                        $strict
                    );
                }

                return;
            }

            // ASSOCIATIVE ARRAY
            if ($strict) {
                self::assertNoExtraKeys($actual, $expected, $path);
            }

            foreach ($expected as $key => $expValue) {

                if (!\array_key_exists($key, $actual)) {
                    self::fail(\sprintf("Missing key '%s' at %s", $key, $path));
                }

                self::compare(
                    $actual[$key],
                    $expValue,
                    \sprintf('%s.%s', $path, $key),
                    $strict
                );
            }

            return;
        }

        // SCALAR strict compare
        if ($actual !== $expected) {
            self::fail(
                \sprintf('Value mismatch at %s%s', $path, PHP_EOL).
                'Expected: '.\var_export($expected, true)."\n".
                'Actual: '.\var_export($actual, true)
            );
        }
    }

    /**
     * @param mixed[] $actual
     * @param mixed[] $expected
     */
    private static function assertNoExtraKeys(array $actual, array $expected, string $path): void
    {
        $extraKeys = \array_diff(\array_keys($actual), \array_keys($expected));

        if ([] !== $extraKeys) {
            self::fail(
                \sprintf('Unexpected keys at %s: ', $path).\implode(', ', $extraKeys)
            );
        }
    }
}
