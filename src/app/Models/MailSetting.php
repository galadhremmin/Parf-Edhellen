<?php

namespace App\Models;

class MailSetting extends ModelBase
{
    protected $fillable = [
        'account_id',
        'forum_post_created',
        'forum_contribution_approved',
        'forum_contribution_rejected',
        'forum_posted_on_profile',
    ];

    protected $primaryKey = 'account_id';

    public $incrementing = false;

    use Traits\HasAccount;
}
