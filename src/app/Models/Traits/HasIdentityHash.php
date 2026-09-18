<?php

namespace App\Models\Traits;

/**
 * Backs an `identity_hash` unique key for tables whose identity spans nullable or accent-insensitive columns,
 * which a composite unique index can't enforce.
 */
trait HasIdentityHash
{
    /**
     * @return string[] the columns that together identify a row
     */
    abstract public static function identityColumns(): array;

    public static function identityHash(array $attributes): string
    {
        $values = array_map(function (string $column) use ($attributes) {
            $value = $attributes[$column] ?? null;

            // Rows read back from the database and attributes built in PHP must hash identically: 5, '5' and
            // true/1 are the same stored value, while NULL stays distinct from 0.
            return $value === null ? null : (string) (is_bool($value) ? (int) $value : $value);
        }, static::identityColumns());

        return md5(json_encode($values));
    }
}
