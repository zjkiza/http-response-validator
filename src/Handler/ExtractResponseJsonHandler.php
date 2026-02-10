<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Handler;

use Symfony\Contracts\HttpClient\ResponseInterface;
use ZJKiza\HttpResponseValidator\Contract\HandlerInterface;
use ZJKiza\HttpResponseValidator\Exception\RuntimeException;
use ZJKiza\HttpResponseValidator\Handler\Factory\TagIndexMethod;
use ZJKiza\HttpResponseValidator\Monad\Result;

use function ZJKiza\HttpResponseValidator\addIdInMessage;

/**
 * @psalm-suppress LessSpecificImplementedReturnType
 * @psalm-suppress MoreSpecificReturnType
 *
 * @implements HandlerInterface<ResponseInterface, array<string,mixed>|object>
 */
final class ExtractResponseJsonHandler extends AbstractHandler implements HandlerInterface
{
    use TagIndexMethod;

    private bool $associative = true;

    /**
     * @param ResponseInterface $value
     *
     * @retrun Result<array<string,mixed>|object>
     */
    public function __invoke(mixed $value): Result
    {
        try {
            /** @var array<string,mixed>|object $data */
            $data = \json_decode($value->getContent(false), $this->associative, 512, JSON_THROW_ON_ERROR);

            return Result::success($data);
        } catch (\Throwable $exception) {
            $message = \sprintf('%s[ExtractResponseJsonHandler] %s', addIdInMessage(), $exception->getMessage());

            /** @var Result<array<string,mixed>|object> */
            return $this->fail(new RuntimeException($message));
        }
    }

    public function setAssociative(bool $associative = true): self
    {
        $this->associative = $associative;

        return $this;
    }
}
