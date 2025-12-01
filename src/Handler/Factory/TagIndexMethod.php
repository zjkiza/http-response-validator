<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Handler\Factory;

trait TagIndexMethod
{
    public static function getIndex(): string
    {
        return self::class;
    }
}
