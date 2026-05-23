<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Contract;

interface ExpectedTypeInterface
{
    public function describe(): string;
}
