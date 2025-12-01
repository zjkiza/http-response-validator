<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator;

function addIdInMessage(string $message = ''): string
{
    $messageIdText = 'Message ID=';

    if (\str_contains($message, $messageIdText)) {
        return $message;
    }

    return \sprintf('%s%s : %s', $messageIdText, generateErrorId(), $message);
}

function generateErrorId(int $length = 16): string
{
    if ($length < 1) {
        throw new \InvalidArgumentException('Length must be greater than 0.');
    }

    return \bin2hex(\random_bytes($length));
}
