<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;

class MailSettingOverride extends ModelBase
{
    protected $fillable = [
        'account_id',
        'entity_type',
        'entity_id',
        'disabled',
    ];

    protected $primaryKey = 'account_id';

    public $incrementing = false;

    use Traits\HasAccount;

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
