<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    const CONTEXT_TRANSLATION         = 10;
    const CONTEXT_SENTENCE            = 20;
    const CONTEXT_PROFILE             = 30;
    const CONTEXT_COMMENT             = 40;

    const ACTION_TRANSLATION_ADD      = 10;
    const ACTION_TRANSLATION_EDIT     = 20;
    const ACTION_TRANSLATION_RESERVED = 30;
    
    const ACTION_SENTENCE_ADD         = 20;
    const ACTION_SENTENCE_EDIT        = 21;
    const ACTION_SENTENCE_RESERVED    = 22;

    const ACTION_PROFILE_FIRST_TIME   = 30;
    const ACTION_PROFILE_EDIT         = 31;
    const ACTION_PROFILE_EDIT_AVATAR  = 32;

    const ACTION_COMMENT_ADD          = 40;
    const ACTION_COMMENT_EDIT         = 41;
    const ACTION_COMMENT_LIKE         = 42;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id', 'entity_context_id', 'entity_id', 'action_id'
    ];

    public function account() {
        return $this->belongsTo(Account::class);
    }
}
