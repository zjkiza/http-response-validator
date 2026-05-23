<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\HttpLogger;

final class ResponseBodyFormatter
{
    /**
     * @param array<string, string> $sensitiveKeys
     */
    public function format(string $content, array $sensitiveKeys): string
    {
        try {
            /** @var array<string, mixed> $decoded */
            $decoded = \json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            $encoded = \json_encode($this->maskSensitiveData($decoded, $sensitiveKeys), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            return \is_string($encoded) ? $encoded : $content;
        } catch (\Throwable) {
            return $content;
        }
    }

    /**
     * @param array<string, mixed>  $data
     * @param array<string, string> $sensitiveKeys
     *
     * @return array<string, mixed>
     */
    private function maskSensitiveData(array $data, array $sensitiveKeys): array
    {
        foreach ($data as $key => $value) {
            if (isset($sensitiveKeys[$key])) {
                $data[$key] = '***';
                continue;
            }

            if (!\is_array($value)) {
                continue;
            }

            /** @var array<string, mixed> $value */
            $data[$key] = $this->maskSensitiveData($value, $sensitiveKeys);
        }

        return $data;
    }
}
