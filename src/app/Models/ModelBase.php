<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

abstract class ModelBase extends Model
{
    protected $dates = [
        'created_at',
        'updated_at'
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
}
