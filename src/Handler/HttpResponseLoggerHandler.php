<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Handler;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use ZJKiza\HttpResponseValidator\Contract\HandlerInterface;
use ZJKiza\HttpResponseValidator\Handler\Factory\TagIndexMethod;
use ZJKiza\HttpResponseValidator\HttpLogger\HttpResponseLogger;
use ZJKiza\HttpResponseValidator\Monad\Result;

/**
 * @implements HandlerInterface<ResponseInterface, ResponseInterface|string>
 */
final class HttpResponseLoggerHandler extends AbstractHandler implements HandlerInterface
{
    use TagIndexMethod;

    private int $expectedStatus = 200;

    /** @var string[] */
    private array $sensitiveKeys = [];

    public function __construct(
        LoggerInterface $logger,
        private readonly HttpResponseLogger $responseLogger,
    ) {
        parent::__construct($logger);
    }

    /**
     * @param ResponseInterface $value
     *
     * @return Result<ResponseInterface|string>
     */
    public function __invoke(mixed $value): Result
    {
        try {

            if ((bool) $this->sensitiveKeys) {
                $this->responseLogger->addSensitiveKeys($this->sensitiveKeys);
            }

            $this->responseLogger->validateResponse($value, $this->expectedStatus);

            /** @var Result<ResponseInterface|string> */
            return Result::success($value);
        } catch (\Throwable $exception) {
            /** @var Result<ResponseInterface|string> */
            return $this->fail($exception->getMessage(), $exception::class, (int) $exception->getCode());
        }
    }

    public function setExpectedStatus(int $expectedStatus = 200): self
    {
        $this->expectedStatus = $expectedStatus;

        return $this;
    }

    /**
     * TODO - add validator for string[].
     *
     * @param string[] $addKeys
     */
    public function addSensitiveKeys(array $addKeys = []): self
    {
        $this->sensitiveKeys = $addKeys;

        return $this;
    }
}
