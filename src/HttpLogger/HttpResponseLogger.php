<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\HttpLogger;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

use function ZJKiza\HttpResponseValidator\addIdInMessage;

final class HttpResponseLogger
{
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

    public function validateResponse(ResponseInterface $response, int $expected = Response::HTTP_OK): void
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode === $expected) {
            return;
        }

        $context = \sprintf('[HttpRequestLogger ERROR CODE] Unexpected status code %d expected %d', $statusCode, $expected);
        $messageId = addIdInMessage();
        $message = \sprintf('%s%s', $messageId, $context);

        $this->logger->error(
            $message,
            ['http_request_failed' => $this->httpRequestFailed($response)]
        );

        throw $this->httpExceptionFactory->create($statusCode, $message, $messageId);
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
     * @return array{
     *      method: string,
     *      url: string,
     *      code: int,
     *      body: string
     * }
     */
    private function httpRequestFailed(ResponseInterface $response): array
    {
        /** @var array<string, mixed> $info */
        $info = $response->getInfo();

        return [
            'method' => isset($info['http_method']) && \is_string($info['http_method'])
                ? $info['http_method']
                : 'UNKNOWN',
            'url' => isset($info['url']) && \is_string($info['url'])
                ? $info['url']
                : 'UNKNOWN',
            'code' => isset($info['http_code']) && \is_int($info['http_code'])
                ? $info['http_code']
                : $response->getStatusCode(),
            'body' => $this->responseBodyFormatter->format($response->getContent(false), $this->sensitiveKeys),
        ];
    }
}
