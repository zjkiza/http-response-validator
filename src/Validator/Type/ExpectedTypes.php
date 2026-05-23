<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Validator\Type;

use ZJKiza\HttpResponseValidator\Enum\TypeCheck;

final class ExpectedTypes
{
    public static function union(TypeCheck ...$types): UnionExpectedType
    {
        return new UnionExpectedType(...$types);
    }

    public static function arrayOf(TypeCheck $type): ArrayOfExpectedType
    {
        return new ArrayOfExpectedType($type);
    }
}
