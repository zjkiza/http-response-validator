<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Contract;

interface ResultInterface
{
    public static function success(mixed $value): self;

    public static function failure(mixed $error): self;
}
