<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Validator\Type;

use ZJKiza\HttpResponseValidator\Contract\ExpectedTypeInterface;
use ZJKiza\HttpResponseValidator\Enum\TypeCheck;

final readonly class UnionExpectedType implements ExpectedTypeInterface
{
    /** @var non-empty-list<TypeCheck> */
    private array $types;

    public function __construct(TypeCheck ...$types)
    {
        if (false === (bool) $types) {
            throw new \InvalidArgumentException('UnionExpectedType requires at least one type.');
        }

        /** @var non-empty-list<TypeCheck> $normalizedTypes */
        $normalizedTypes = \array_values($types);

        $this->types = $normalizedTypes;
    }

    /** @return non-empty-list<TypeCheck> */
    public function types(): array
    {
        return $this->types;
    }

    public function describe(): string
    {
        return \implode('|', \array_map(static fn (TypeCheck $type): string => $type->value, $this->types));
    }
}
