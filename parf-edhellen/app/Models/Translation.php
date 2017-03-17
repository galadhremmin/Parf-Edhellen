<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $table = 'translation';
    protected $primaryKey = 'TranslationID';
    protected $dates = [ 'DateCreated' ];

    public function author() {
        return $this->hasOne(Author::class, 'AccountID', 'AuthorID');
    }

    public function group() {
        return $this->hasOne(TranslationGroup::class, 'TranslationGroupID', 'TranslationGroupID');
    }
    
    public function language() {
        return $this->hasOne(Language::class, 'ID', 'LanguageID');
    }

    public function word() {
        return $this->hasOne(Word::class, 'KeyID', 'WordID');
    }

    public function scopeFindForWord($query, $word) {
        /*
        'SELECT w.`Key` AS `Word`, t.`TranslationID`, t.`Translation`, t.`Etymology`,
           t.`Type`, t.`Source`, t.`Comments`, t.`Tengwar`, t.`Phonetic`,
           l.`Name` AS `Language`, t.`NamespaceID`, l.`Invented` AS `LanguageInvented`,
           t.`AuthorID`, a.`Nickname`, w.`NormalizedKey`, t.`Index`,
           t.`DateCreated`, tg.`TranslationGroupID`, tg.`Name` AS `TranslationGroup`,
           tg.`Canon`, tg.`ExternalLinkFormat`, t.`Uncertain`, t.`ExternalID`
         FROM `translation` t
         INNER JOIN `word` w ON w.`KeyID` = t.`WordID`
         INNER JOIN `language` l ON l.`ID` = t.`LanguageID`
         LEFT JOIN `auth_accounts` a ON a.`AccountID` = t.`AuthorID`
         LEFT JOIN `translation_group` tg ON tg.`TranslationGroupID` = t.`TranslationGroupID`
         WHERE t.`NamespaceID` IN('.$senseIDs.') AND t.`Latest` = 1 AND t.`Deleted` = b\'0\'
         ORDER BY l.`Order` ASC, t.`NamespaceID` ASC, l.`Name`
         DESC, w.`Key` ASC';
        */

        $senses = Sense::findForWord($word)
            ->get();

        return $query
            ->where([
                ['Latest', '=', 1],
                ['Deleted', '=', 0]
            ])
            ->whereIn('NamespaceID', $senses);
    }
}
