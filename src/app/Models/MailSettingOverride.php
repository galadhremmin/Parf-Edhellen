<?php

namespace App\Models;

class MailSettingOverride extends ModelBase
{
    protected $guarded = [];
    protected $primaryKey = 'account_id';
    public $incrementing = false;

    use Traits\HasAccount;
}
