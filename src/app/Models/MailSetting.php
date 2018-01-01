<?php

namespace App\Models;

class MailSetting extends ModelBase
{
    protected $guarded = [];
    protected $primaryKey = 'account_id';
    public $incrementing = false;

    use Traits\HasAccount;

    
}
