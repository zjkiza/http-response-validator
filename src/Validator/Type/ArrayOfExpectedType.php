<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Validator\Type;

use ZJKiza\HttpResponseValidator\Contract\ExpectedTypeInterface;
use ZJKiza\HttpResponseValidator\Enum\TypeCheck;

final readonly class ArrayOfExpectedType implements ExpectedTypeInterface
{
    public function __construct(private TypeCheck $type)
    {
    }

    public function type(): TypeCheck
    {
        return $this->type;
    }

    public function describe(): string
    {
        return $this->type->value.'[]';
    }
}
