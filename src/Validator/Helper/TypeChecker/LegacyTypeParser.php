<?php

declare(strict_types=1);

namespace ZJKiza\HttpResponseValidator\Validator\Helper\TypeChecker;

final class LegacyTypeParser
{
    public static function isArrayOfNotation(string $type): bool
    {
        return \str_ends_with($type, '[]');
    }

    public static function innerType(string $type): string
    {
        return \substr($type, 0, -2);
    }

    /** @return non-empty-list<string> */
    public static function unionTypes(string $type): array
    {
        // TODO(remove-legacy-type-strings): drop string union parsing after BC window closes.
        $types = \array_map(\trim(...), \explode('|', $type));
        $types = \array_values(\array_filter($types, static fn (string $item): bool => '' !== $item));

        if ([] === $types) {
            return [$type];
        }

        return $types;
    }
}
