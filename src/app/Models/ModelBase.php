<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

abstract class ModelBase extends Model
{
    protected $dates = [
        Model::CREATED_AT,
        Model::UPDATED_AT
        // 'deleted_at' <-- presently not supported
    ];

    public function hasAttribute($attr)
    {
        return array_key_exists($attr, $this->attributes);
    }
    
    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->toAtomString();
    }

    public function equals($gloss)
    {
        if ($gloss === null || $gloss instanceof Gloss === false) {
            return false;
        }

        $equals = $this === $gloss;

        if (! $equals) {
            $equals = true;
            $attributeNames = $this->getAttributes();

            foreach ($attributeNames as $attributeName => $attribute) {
                if ($attributeName === $this->primaryKey ||
                    in_array($attributeName, $this->dates) ||
                    ! array_key_exists($attributeName, $gloss->attributes)) {
                    continue;
                }

                $otherAttribute = $gloss->getAttribute($attributeName);
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
