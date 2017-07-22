<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\{ AuditTrail, Keyword, Translation, Sense, Word };
use App\Helpers\StringHelper;

class TranslationRepository
{
    protected $_auditTrail;

    public function __construct(AuditTrailRepository $auditTrail)
    {
        $this->_auditTrail = $auditTrail;
    }

    public function getKeywordsForLanguage(string $word, $reversed = false, $languageId = 0) 
    {
        $word = self::formatWord($word);

        if ($languageId > 0) {
            $query = DB::table('keywords as k')
                ->join('translations as t', 'k.sense_id', 't.sense_id')
                ->where([
                    [ 't.is_latest', '=', 1 ],
                    [ 't.is_deleted', '=', 0 ],
                    [ 't.language_id', '=', $languageId ],
                    [ $reversed ? 'k.reversed_normalized_keyword_unaccented' : 'k.normalized_keyword_unaccented', 'like', $word ]
                ])
                ->select('k.keyword as k', 'k.normalized_keyword as nk')
                ->distinct();
        } else {
            $query = Keyword::findByWord($word, $reversed)
                ->select('keyword as k', 'normalized_keyword as nk');
        }

        $keywords = $query
            ->orderBy(DB::raw('CHAR_LENGTH(k)'), 'asc')
            ->limit(100)
            ->get();

        return $keywords;
    }

    public function getWordTranslations(string $word, int $languageId = 0) 
    {
        $senses = self::getSensesForWord($word);
        return self::createTranslationQuery($languageId)
            ->whereIn('t.sense_id', $senses)
            ->orderBy('word')
            ->get()
            ->toArray();
    }

    public function getTranslation(int $id) 
    {
        return self::createTranslationQuery()
            ->where('t.id', $id)
            ->first();
    }

    public function getTranslationListForLanguage(int $languageId)
    {
        $translations = Translation::where('language_id', $languageId)
            ->join('words as w', 'word_id', 'w.id')
            ->join('accounts as u', 'translations.account_id', 'u.id')
            ->leftJoin('speeches as s', 'speech_id', 's.id')
            ->leftJoin('words as ws', 'sense_id', 'ws.id')
            ->active()
            ->select('translations.id', 'translation', 'w.word', 'source', 'u.nickname as account_name', 
                'translations.account_id', 'is_rejected', 's.name as speech', 'ws.word as sense')
            ->orderBy('w.word')
            ->get();
        
        return $translations;
    }

    public function getVersions(int $id)
    {
        $translation = Translation::select()
            ->where('id', $id)
            ->select('id', 'origin_translation_id')
            ->first();

        if ($translation === null) {
            return [];  
        }

        return self::createTranslationQuery(0, false)
            ->where(function ($query) use($translation) {

                if ($translation->origin_translation_id) {
                    $query->where('t.id', $translation->origin_translation_id)
                        ->orWhere('t.origin_translation_id', $translation->origin_translation_id);
                } else {
                    $query->where('t.id', $translation->id)
                        ->orWhere('t.origin_translation_id', $translation->id);
                }
                
            })
            ->orderBy('t.id', 'asc')
            ->get()
            ->toArray();
    }

    public function suggest(array $words, int $languageId, $inexact = false) 
    {
        // Transform all words to lower case and remove doublettes.
        $words = array_unique(array_map(function ($s) {
            return mb_strtolower($s, 'utf-8');
        }, $words));

        // Create an array containing words in their ASCII form. These
        // will be used to query the database with.
        $normalizedWords = array_unique(array_map(function ($s) use($inexact) {
            $w = StringHelper::normalize($s);
            if ($inexact) {
                $w .= '%';
            }
            return $w;
        }, $words));

        // Initialize the grouped suggestions hash array with an empty array
        // per word. An empty array will tell the caller that no suggestions
        // were found.
        $groupedSuggestions = [];
        foreach ($words as $word) {
            $groupedSuggestions[$word] = [];
        }

        $numberOfNormalizedWords = count($normalizedWords);
        if ($numberOfNormalizedWords < 1) {
            return $groupedSuggestions;
        }

        $query = self::createTranslationQuery($languageId);
        
        if ($inexact) {
            $query->where(function ($query) use ($normalizedWords) {
                foreach ($normalizedWords as $normalizedWord) {
                    $query->orWhere('w.normalized_word', 'like', $normalizedWord);
                }
            });
        } else {
            $query = $query->whereIn('w.normalized_word', $normalizedWords);
        }

        $suggestions = $query
            ->orderBy(DB::raw('CHAR_LENGTH(w.normalized_word)'))
            ->limit($numberOfNormalizedWords*15)
            ->get()
            ->toArray();
        
        if (count($suggestions) > 0) {
            foreach ($words as $word) {
                $lengthOfWord = strlen($word);
                
                // Try to find direct matches first, i.e. á => á.
                $matchingSuggestions = array_filter($suggestions, function($s) use($word, $lengthOfWord) {
                    return strlen($s->word) >= $lengthOfWord && substr($word, 0, $lengthOfWord) === $word;
                });

                if (count($matchingSuggestions) < 1) {
                    // If no direct matches were found, normalize the word and try again, i.e. a => a
                    $normalizedWord = StringHelper::normalize($word);
                    $lengthOfWord = strlen($normalizedWord);

                    $matchingSuggestions = array_filter($suggestions, function ($s) use ($normalizedWord, $lengthOfWord) {
                        return strlen($s->normalized_word) >= $lengthOfWord && 
                            substr($s->normalized_word, 0, $lengthOfWord) === $normalizedWord;
                    });
                }

                $groupedSuggestions[$word] = $matchingSuggestions;
            }
        }

        return $groupedSuggestions;
    }

    public function saveTranslation(string $wordString, string $senseString, Translation $translation, array $keywords, $resetKeywords = true)
    {
        // 1. Turn all words should be lower case.
        $wordString  = StringHelper::toLower($wordString);
        $senseString = StringHelper::toLower($senseString);
        $glossString = StringHelper::toLower($translation->translation);

        // 2. Retrieve existing or create a new word entity for the sense and the word.
        $word      = $this->createWord($wordString, $translation->account_id);
        $senseWord = $this->createWord($senseString, $translation->account_id);
        $glossWord = $this->createWord($glossString, $translation->account_id);

        // 3. Load sense or create it if it doesn't exist. A sense is 1:1 mapped with
        // words, and therefore doesn't have its own incrementing identifier.
        $sense = $this->createSense($senseWord);

        $translation->word_id  = $word->id;
        $translation->sense_id = $sense->id;

        // 4. Load the original translation and update the translation's origin and parent columns.
        $changed = true;
        $originalTranslation = null;
        if ($translation->id) {
            $originalTranslation = Translation::findOrFail($translation->id)
                ->getLatestVersion();

            // 5. were there changes made?
            $newAttributes = $translation->attributesToArray();
            $oldAttributes = $originalTranslation->attributesToArray();

            $changed = false;
            foreach ($newAttributes as $key => $value) {
                // avoid perfect equality (===/!==) because the value in the DB
                // can diverge from the one passed from the view.
                if ($oldAttributes[$key] != $value) {
                    $changed = true;
                    break;
                }
            }

            if ($changed) {
                $translation = $translation->replicate();
                $translation->origin_translation_id = $originalTranslation->origin_translation_id ?: $originalTranslation->id;

                // 6. If the sense has changed, check whether the previous sense should be excluded from
                // the keywords table, which should only contain keywords to current senses.
                if ($originalTranslation->sense_id !== $sense->id) {
                    $originalSense = Sense::find($originalTranslation->sense_id);
                    // is the original translation the only one associated with this sense?
                    if ($originalSense !== null && $originalSense->translations()->count() === 1) {
                        // delete the sense's keywords as the sense is no longer in use.
                        $originalSense->keywords()->delete();
                    }
                }
            }
        }

        if (! $translation->word_id)
            throw new \Exception('Invalid word "'.$wordString.'" ('.$word->id.'). Object: '.print_r($translation, true));

        if (! $translation->sense_id)
            throw new \Exception('Invalid sense "'.$senseString.'" ('.$sense->id.'). Object: '.print_r($translation, true));

        // 7. Save changes as a _new_ row.
        if ($changed) {
            $translation->is_latest = 1;
            $translation->is_deleted = 0;
            $translation->is_index = 0;
            $translation->save();

            // 8. Update existing associations to the new entity.
            if ($originalTranslation !== null) {
                $originalTranslation->child_translation_id = $translation->id;
                $originalTranslation->is_latest = 0;
                $originalTranslation->save();

                $originalTranslation->sentence_fragments()->update([
                    'translation_id' => $translation->id
                ]);
                $originalTranslation->translation_reviews()->update([
                    'translation_id' => $translation->id
                ]);
                $originalTranslation->favourites()->update([
                    'translation_id' => $translation->id
                ]);
            }
        }

        // 9. Remove existing keywords
        if ($originalTranslation !== null) {
            $originalTranslation->keywords()->delete();
        }

        // 10. save gloss and word as keywords on the translation
        $this->createKeyword($word, $sense, $translation);
        if ($word->id !== $glossWord->id) { // this is sometimes possible (most often with names)
            $this->createKeyword($glossWord, $sense, $translation);
        }

        // 11. Register keywords on the sense
        
        // 11a. Process keywords -- filter through the keywords and remove keywords that
        // match the gloss and the translation's word, as these are managed separately.
        $keywords = array_filter($keywords, function ($w) use($wordString, $glossString) {
            return $w !== $wordString && $w !== $glossString;
        });

        // 11b. Delete existing keywords associated with the sense.
        if ($resetKeywords) {
            $sense->keywords()->whereNull('translation_id')->delete();
        }

        // 11c. Recreate the keywords for the sense.
        foreach ($keywords as $keyword) {
            $keywordWord = $this->createWord($keyword, $translation->account_id);

            if ($sense->keywords()->where('word_id', $keywordWord->id)->count() < 1) {
                $this->createKeyword($keywordWord, $sense, null);
            }
        }

        // 12. Register an audit trail
        $action = $changed && $originalTranslation !== null
                ? AuditTrail::ACTION_TRANSLATION_EDIT 
                : AuditTrail::ACTION_TRANSLATION_ADD;
        $this->_auditTrail->store($action, $translation->account_id, $translation);

        return $translation;
    }

    public function deleteTranslationWithId(int $id, int $replaceId)
    {
        $translation = Translation::findOrFail($id);

        // Deleted translations or deprecated (replaced) translations cannot be deleted.
        if ($translation->is_deleted || ! $translation->is_latest) {
            return false;
        }

        // Only indexes can be permanently deleted (DELETE).
        $permanentDeletion = $translation->is_index;

        if ($permanentDeletion) {
            // Delete all indexes in their entirety.
            $t = $translation->getOrigin();
            while ($t) {
                $child = $t->getChild();
                $this->deleteTranslation($t, 0);
                $t = $child;
            }
        } else {
            $this->deleteTranslation($translation, $replaceId);
        }

        return true;
    }

    protected static function getSensesForWord(string $word) 
    {
        $rows = DB::table('keywords')
            ->where('normalized_keyword', $word)
            ->select('sense_id')
            ->distinct()
            ->get();

        $ids = array();
        foreach ($rows as $row)
            $ids[] = $row->sense_id;

        return $ids;
    }

    protected function createWord(string $wordString, int $accountId)
    {
        $wordString = mb_strtolower(trim($wordString), 'utf-8');
        $word = Word::whereRaw('BINARY word = ?', [ $wordString ])->first(); 

        if (! $word) {
            $normalizedWordString = StringHelper::normalize($wordString);
            
            $word = new Word;
            $word->word                     = $wordString;
            $word->normalized_word          = $normalizedWordString;
            $word->reversed_normalized_word = strrev($normalizedWordString); 
            $word->account_id               = $accountId;

            $word->save();
        }

        return $word; 
    }

    protected function createSense(Word $senseWord)
    {
        $sense = Sense::find($senseWord->id);
        
        if (! $sense) {
            $sense = new Sense;
            $sense->id = $senseWord->id;
            $sense->description = $senseWord->word;

            $sense->save();
        }

        return $sense;
    }

    protected function createKeyword(Word $word, Sense $sense, Translation $translation = null)
    {
        $keyword = new Keyword;

        $keyword->keyword                     = $word->word;
        $keyword->word_id                     = $word->id;
        $keyword->sense_id                    = $sense->id;

        // Normalized keywords are primarily used for direct references, where accents do matter. A direct reference
        // can be _miiir_ which would match _mîr_ according to the default normalization scheme. See StringHelper for more
        // information.
        $keyword->normalized_keyword          = $word->normalized_word;
        $keyword->reversed_normalized_keyword = $word->reversed_normalized_word;

        // Unaccented keywords' columns are used for searching, because _mir_ should find _mir_, _mír_, _mîr_ etc.
        $normalizedUnaccented = StringHelper::normalize($word->word, false);
        $keyword->normalized_keyword_unaccented          = $normalizedUnaccented;
        $keyword->reversed_normalized_keyword_unaccented = strrev($normalizedUnaccented);

        if ($translation) {
            $keyword->translation_id = $translation->id;
            $keyword->is_sense       = 0;
        } else {
            $keyword->is_sense       = 1;
        }

        $keyword->save();
    }

    protected static function createTranslationQuery($languageId = 0, $latest = true) 
    {
        $filters = [
            ['t.is_deleted', '=', 0],
            ['t.is_index', '=', 0]
        ];

        if ($latest) {
            $filters[] = ['t.is_latest', '=', 1];
        }

        $q = DB::table('translations as t')
            ->join('words as w', 't.word_id', 'w.id')
            ->leftJoin('accounts as a', 't.account_id', 'a.id')
            ->leftJoin('translation_groups as tg', 't.translation_group_id', 'tg.id')
            ->leftJoin('speeches as s', 't.speech_id', 's.id')
            ->where($filters);

        if ($languageId !== 0) {
            $q = $q->where('t.language_id', $languageId);
        }

        return $q->select(
                'w.word', 't.id', 't.translation', 't.etymology', 's.name as type', 't.source',
                't.comments', 't.tengwar', 't.phonetic', 't.language_id', 't.account_id',
                'a.nickname as account_name', 'w.normalized_word', 't.is_index', 't.created_at', 't.translation_group_id',
                'tg.name as translation_group_name', 'tg.is_canon', 'tg.external_link_format', 't.is_uncertain', 
                't.external_id', 't.is_latest', 't.is_rejected', 't.origin_translation_id');
    }

    protected function deleteTranslation(Translation $t, int $replaceId) 
    {
        $t->keywords()->delete();

        if (! $t->is_index) {
            $t->sentence_fragments()->update([
                'translation_id' => $replaceId
            ]);
            
            $t->favourites()->update([
                'translation_id' => $replaceId
            ]);
        }

        $t->is_deleted = true;
        $t->save();
    }

    protected static function formatWord(string $word) 
    {
        if (strpos($word, '*') !== false) {
            return str_replace('*', '%', $word);
        } 
        
        return $word.'%';
    }
}