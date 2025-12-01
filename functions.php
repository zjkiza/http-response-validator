<?php

declare(strict_types=1);

namespace Zjkiza\HttpResponseValidator\Functions;

function addIdInMessage(string $message = ''): string
{
    $messageIdText = 'Message ID=';

    if (str_contains($message, $messageIdText)) {
        return $message;
    }

    return sprintf('%s%s : %s', $messageIdText, generateErrorId(), $message);
}

function generateErrorId(int $length = 16): string
{
    return bin2hex(random_bytes($length));
}
