<?php

namespace App\Http\Controllers\Abstracts;

use App\Adapters\BookAdapter;
use App\Models\Versioning\LexicalEntryVersion;
use App\Repositories\DiscussRepository;
use App\Repositories\LexicalEntryInflectionRepository;
use App\Repositories\LexicalEntryRepository;
use App\Repositories\SearchIndexRepository;
use App\Repositories\ValueObjects\SearchIndexSearchValue;
use Illuminate\Http\Request;

abstract class BookBaseController extends Controller
{
    protected DiscussRepository $_discussRepository;

    protected LexicalEntryRepository $_lexicalEntryRepository;

    protected SearchIndexRepository $_searchIndexRepository;

    protected LexicalEntryInflectionRepository $_lexicalEntryInflectionRepository;

    protected BookAdapter $_bookAdapter;

    public function __construct(DiscussRepository $discussRepository, LexicalEntryRepository $lexicalEntryRepository,
        LexicalEntryInflectionRepository $lexicalEntryInflectionRepository, SearchIndexRepository $searchIndexRepository,
        BookAdapter $bookAdapter)
    {
        $this->_discussRepository = $discussRepository;
        $this->_lexicalEntryRepository = $lexicalEntryRepository;
        $this->_searchIndexRepository = $searchIndexRepository;
        $this->_lexicalEntryInflectionRepository = $lexicalEntryInflectionRepository;
        $this->_bookAdapter = $bookAdapter;
    }

    /**
     * Gets the lexical entry with the specified ID. The lexical entry is also adapted for the immediate
     * use as a view model.
     *
     * @param  bool  $coerceLatest
     * @return Collection
     */
    protected function getLexicalEntry(int $lexicalEntryId)
    {
        $lexicalEntries = $this->getLexicalEntryUnadapted($lexicalEntryId);
        if ($lexicalEntries->count() < 1) {
            return collect([]);
        }

        $lexicalEntry = $lexicalEntries->first();
        $comments = $this->_discussRepository->getNumberOfPostsForEntities(LexicalEntryVersion::class, [$lexicalEntry->latest_lexical_entry_version_id]);
        $inflections = $this->_lexicalEntryInflectionRepository->getInflectionsForLexicalEntries([$lexicalEntryId]);

        return $this->_bookAdapter->adaptLexicalEntries([$lexicalEntry], $inflections, $comments, $lexicalEntry->word->word);
    }

    /**
     * Gets the lexical entry with the specified ID. As there might be multiple translations
     * associated with the specified lexical entry, this method might return multiple lexical entries.
     *
     * @param  bool  $coerceLatest
     * @return Collection
     */
    protected function getLexicalEntryUnadapted(int $lexicalEntryId)
    {
        $lexicalEntry = $this->_lexicalEntryRepository->getLexicalEntry($lexicalEntryId);

        if ($lexicalEntry === null) {
            // The lexical entry might still be present in `lexical_entry_versions` and the link consequently
            // incorrect. This may only happen for legacy reasons where each lexical entry version had
            // its own unique ID. All deprecated versions were migrated to the lexical_entry_versions
            // table on 2022-04-25 but their IDs retained as `__migration_gloss_id`.
            $version = $this->_lexicalEntryRepository->getLexicalEntryVersionByPreMigrationId($lexicalEntryId);
            if ($version === null) {
                return collect([]);
            }

            $lexicalEntry = $this->_lexicalEntryRepository->getLexicalEntry($version->lexical_entry_id);
        }

        return $lexicalEntry;
    }

    /**
     * Ensures that the specified request to ensure that it contains the information we need to find entities
     * matching a specified criteria. The overrides is an optional parameter that allows you to override values
     * of your choice, i.e. values that may be passed through the URL path instead of the query string and request
     * payload.
     *
     * @param  array  $overrides  An associate array with key-value pairs representing overrides from the default rule set
     */
    protected function validateFindRequest(Request $request, array $overrides = []): SearchIndexSearchValue
    {
        $rules = [
            'lexical_entry_group_ids' => 'sometimes|array',
            'lexical_entry_group_ids.*' => 'sometimes|numeric',
            'include_old' => 'sometimes|boolean',
            'inflections' => 'sometimes|boolean',
            'language' => 'sometimes|string',
            'language_id' => 'sometimes|numeric',
            'natural_language' => 'sometimes|boolean',
            'reversed' => 'sometimes|boolean',
            'speech_ids' => 'sometimes|array',
            'speech_ids.*' => 'sometimes|numeric',
            'word' => 'required|string',
        ];

        foreach (array_keys($overrides) as $rule) {
            unset($rules[$rule]);
        }
        $v = $request->validate($rules);
        foreach ($overrides as $rule => $value) {
            $v[$rule] = $value;
        }

        $includeOld = isset($v['include_old']) ? boolval($v['include_old']) : true;
        $inflections = isset($v['inflections']) ? boolval($v['inflections']) : false;
        $languageId = isset($v['language_id']) ? intval($v['language_id']) : 0;
        $reversed = isset($v['reversed']) ? boolval($v['reversed']) : false;
        $naturalLanguage = isset($v['natural_language']) ? boolval($v['natural_language']) : false;
        $word = $v['word'];

        $glossGroupIds = isset($v['lexical_entry_group_ids']) ? array_map(function ($v) {
            return intval($v);
        }, $v['lexical_entry_group_ids']) : null;
        $speechIds = isset($v['speech_ids']) ? array_map(function ($v) {
            return intval($v);
        }, $v['speech_ids']) : null;

        $value = new SearchIndexSearchValue([
            'lexical_entry_group_ids' => $glossGroupIds,
            'include_old' => $includeOld,
            'inflections' => $inflections,
            'language_id' => $languageId,
            'natural_language' => $naturalLanguage,
            'reversed' => $reversed,
            'speech_ids' => $speechIds,
            'word' => $word,
        ]);

        return $value;
    }
}
