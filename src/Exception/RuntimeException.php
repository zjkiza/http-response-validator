<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Exception;

use ZJKiza\HttpResponseValidator\Contract\ExceptionInterface;

final class RuntimeException extends \RuntimeException implements ExceptionInterface
{
}
