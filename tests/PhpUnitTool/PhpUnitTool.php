<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Tests\PhpUnitTool;

use PHPUnit\Framework\TestCase;
use function is_array;
use function is_callable;

class PhpUnitTool extends TestCase
{
    public static function assertArrayRecords(array $actual, array $expected): void
    {
        static::assertCount(count($expected), $actual);

        foreach ($expected as $index => $expItem) {
            $actItem = $actual[$index];

            foreach ($expItem as $key => $expValue) {
                static::assertArrayHasKey($key, $actItem);

                $actValue = $actItem[$key];

                //  Ako je callable, pozovi je
                if (is_callable($expValue)) {
                    $expValue($actValue);
                } // Ako je niz, rekurzivno pozovi
                elseif (is_array($expValue)) {
                    static::assertIsArray($actValue);
                    static::assertArrayRecursive($actValue, $expValue);
                } // Ako je vrednost, poredi
                else {
                    static::assertSame($expValue, $actValue);
                }
            }
        }
    }

    private static function assertArrayRecursive(array $actual, array $expected): void
    {
        foreach ($expected as $key => $expValue) {
            static::assertArrayHasKey($key, $actual);

            $actValue = $actual[$key];

            if (is_callable($expValue)) {
                $expValue($actValue);
            } elseif (is_array($expValue)) {
                static::assertIsArray($actValue);
                static::assertArrayRecursive($actValue, $expValue);
            } else {
                static::assertSame($expValue, $actValue);
            }
        }
    }
}