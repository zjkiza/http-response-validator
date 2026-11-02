<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\HttpLogger;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

use function ZJKiza\HttpResponseValidator\addIdInMessage;

final class HttpResponseLogger
{
    private const MASK = '***';

    /**
     * @var array<string, string>
     */
    private array $sensitiveKeys = [
        'password' => 'password',
        'token' => 'token',
        'apiKey' => 'apiKey',
    ];

    private readonly ResponseBodyFormatter $responseBodyFormatter;
    private readonly HttpExceptionFactory $httpExceptionFactory;

    public function __construct(private readonly LoggerInterface $logger)
    {
        $this->responseBodyFormatter = new ResponseBodyFormatter();
        $this->httpExceptionFactory = new HttpExceptionFactory();
    }

    /**
     * @param string[] $sensitiveValues literal values (e.g. a token placed in the URL path) that are masked in the URL and body
     */
    public function validateResponse(ResponseInterface $response, int $expected = Response::HTTP_OK, array $sensitiveValues = []): void
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === $expected) {
            return;
        }

        $context = \sprintf('[HttpRequestLogger ERROR CODE] Unexpected status code %d expected %d', $statusCode, $expected);
        $messageId = addIdInMessage();
        $message = \sprintf('%s%s', $messageId, $context);
        $requestFailed = $this->httpRequestFailed($response, $sensitiveValues);

        $this->logger->error(
            $message,
            ['http_request_failed' => $requestFailed]
        );

        throw $this->httpExceptionFactory->create($statusCode, $message, $messageId, $requestFailed);
    }

    /**
     * @param string[] $addKeys
     */
    public function addSensitiveKeys(array $addKeys): void
    {
        foreach ($addKeys as $key) {
            if (isset($this->sensitiveKeys[$key])) {
                continue;
            }

            $this->sensitiveKeys[$key] = $key;
        }
    }

    /**
     * @param string[] $sensitiveValues
     *
     * @return array{
     *      method: string,
     *      url: string,
     *      code: int,
     *      body: string
     * }
     */
    private function httpRequestFailed(ResponseInterface $response, array $sensitiveValues): array
    {
        /** @var array<string, mixed> $info */
        $info = $response->getInfo();

        $url = isset($info['url']) && \is_string($info['url'])
            ? $info['url']
            : 'UNKNOWN';

        return [
            'method' => isset($info['http_method']) && \is_string($info['http_method'])
                ? $info['http_method']
                : 'UNKNOWN',
            'url' => $this->maskValues($this->maskQuery($url), $sensitiveValues),
            'code' => isset($info['http_code']) && \is_int($info['http_code'])
                ? $info['http_code']
                : $response->getStatusCode(),
            'body' => $this->maskValues(
                $this->responseBodyFormatter->format($response->getContent(false), $this->sensitiveKeys),
                $sensitiveValues
            ),
        ];
    }

    /**
     * Masks the values of the sensitive keys in the URL query string.
     */
    private function maskQuery(string $url): string
    {
        foreach ($this->sensitiveKeys as $key) {
            $url = (string) \preg_replace(
                \sprintf('/([?&]%s=)[^&#]*/i', \preg_quote($key, '/')),
                '$1'.self::MASK,
                $url
            );
        }

        return $url;
    }

    /**
     * @param string[] $sensitiveValues
     */
    private function maskValues(string $content, array $sensitiveValues): string
    {
        foreach ($sensitiveValues as $value) {
            if ('' === $value) {
                continue;
            }

            $content = \str_replace($value, self::MASK, $content);
        }

        return $content;
    }
}
