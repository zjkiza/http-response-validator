<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Exception;

use ZJKiza\HttpResponseValidator\Contract\ExceptionInterface;

final class InvalidArgumentException extends \RuntimeException implements ExceptionInterface
{
}
