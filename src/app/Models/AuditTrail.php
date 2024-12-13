<?php

namespace App\Models;

class AuditTrail extends ModelBase
{
    use Traits\HasAccount;

    const ACTION_GLOSS_ADD                = 10;
    const ACTION_GLOSS_EDIT               = 11;
    const ACTION_GLOSS_DELETE             = 12;
    
    const ACTION_SENTENCE_ADD             = 20;
    const ACTION_SENTENCE_EDIT            = 21;
    const ACTION_SENTENCE_DELETE          = 22;

    const ACTION_PROFILE_FIRST_TIME       = 30;
    const ACTION_PROFILE_EDIT             = 31;
    const ACTION_PROFILE_EDIT_AVATAR      = 32;
    const ACTION_PROFILE_AUTHENTICATED    = 33;
    const ACTION_PROFILE_CHANGED_PASSWORD = 34;
    const ACTION_PROFILE_CREATED          = 35;
    const ACTION_PROFILE_MERGED           = 36;
    const ACTION_PROFILE_DELETE           = 37;

    const ACTION_COMMENT_ADD              = 40;
    const ACTION_COMMENT_EDIT             = 41;
    const ACTION_COMMENT_LIKE             = 42;

    const ACTION_FLASHCARD_FIRST_CARD     = 50;
    const ACTION_FLASHCARD_CARD_10        = 51;
    const ACTION_FLASHCARD_CARD_50        = 52;
    const ACTION_FLASHCARD_CARD_100       = 53;
    const ACTION_FLASHCARD_CARD_200       = 54;
    const ACTION_FLASHCARD_CARD_500       = 55;

    const ACTION_MAIL_VERIFY_DISPATCHED   = 60;
    const ACTION_MAIL_VERIFY_VERIFIED     = 61;
    const ACTION_MAIL_VERIFY_UNVERIFIED   = 62;

    const ACTION_SEARCH_GLOSSARY          = 70;
    
    const ACTION_CONTRIBUTION_APPROVE     = 80;
    const ACTION_CONTRIBUTION_REJECT      = 81;
    const ACTION_CONTRIBUTION_DELETE      = 82;

    const ACTION_ACCOUNT_ADMIN_VIEW       = 1000;
    const ACTION_ACCOUNT_ADD_ROLE         = 1001;
    const ACTION_ACCOUNT_REMOVE_ROLE      = 1002;

    const ACTION_INFLECTION_ADD           = 1010;
    const ACTION_INFLECTION_EDIT          = 1011;

    const ACTION_SPEECH_ADD               = 1020;
    const ACTION_SPEECH_EDIT              = 1021;

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
