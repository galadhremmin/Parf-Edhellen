<?php

namespace App\Models;

class AccountRoleRel extends ModelBase
{
    protected $fillable = ['account_id', 'role_id'];

    use Traits\HasAccount;
}
