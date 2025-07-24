<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int|string $id
 */
abstract class ModelBase extends Model
{
    protected $casts = [
        Model::CREATED_AT => 'datetime',
        Model::UPDATED_AT => 'datetime',
    ];

    public function hasAttribute($attr)
    {
        return array_key_exists($attr, $this->attributes);
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date?->format(DateTimeInterface::ATOM);
    }

    public function equals($entity)
    {
        if (! $entity instanceof ModelBase) {
            return false;
        }

        $equals = $this === $entity;

        if (! $equals) {
            $equals = true;
            $attributeNames = $this->getAttributes();

            foreach ($attributeNames as $attributeName => $attribute) {
                if ($attributeName === $this->primaryKey ||
                    // deliberately don't compare date times as they mutate when entities are persisted.
                    (array_key_exists($attributeName, $this->casts) && $this->casts[$attributeName] === 'datetime') ||
                    ! array_key_exists($attributeName, $entity->attributes)) {
                    continue;
                }

                $otherAttribute = $entity->getAttribute($attributeName);
                if ($attribute instanceof ModelBase) {
                    $equals = $attribute->equals($otherAttribute);
                } else {
                    // Deliberately use `==` to support type casting. A typical example
                    // is supporting boolean values that are stored as `1/0` in the database.
                    $equals = $attribute == $otherAttribute;
                }

                if (! $equals) {
                    break;
                }
            }
        }

        return $equals;
    }
}
