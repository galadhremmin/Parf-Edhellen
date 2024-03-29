<?php

namespace App\Models;

class AuditTrail extends ModelBase
{
    use Traits\HasAccount;

    const ACTION_GLOSS_ADD                = 10;
    const ACTION_GLOSS_EDIT               = 11;
    const ACTION_GLOSS_RESERVED           = 12;
    
    const ACTION_SENTENCE_ADD             = 20;
    const ACTION_SENTENCE_EDIT            = 21;
    const ACTION_SENTENCE_RESERVED        = 22;

    const ACTION_PROFILE_FIRST_TIME       = 30;
    const ACTION_PROFILE_EDIT             = 31;
    const ACTION_PROFILE_EDIT_AVATAR      = 32;
    const ACTION_PROFILE_AUTHENTICATED    = 33;
    const ACTION_PROFILE_CHANGED_PASSWORD = 34;
    const ACTION_PROFILE_CREATED          = 35;
    const ACTION_PROFILE_MERGED           = 36;

    const ACTION_COMMENT_ADD              = 40;
    const ACTION_COMMENT_EDIT             = 41;
    const ACTION_COMMENT_LIKE             = 42;

    const ACTION_FLASHCARD_FIRST_CARD     = 50;
    const ACTION_FLASHCARD_CARD_10        = 51;
    const ACTION_FLASHCARD_CARD_50        = 52;
    const ACTION_FLASHCARD_CARD_100       = 53;
    const ACTION_FLASHCARD_CARD_200       = 54;
    const ACTION_FLASHCARD_CARD_500       = 55;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id', 'entity_type', 'entity_id', 'action_id', 'is_admin', 'entity_name'
    ];

    public function entity() 
    {
        return $this->morphTo();
    }
}
