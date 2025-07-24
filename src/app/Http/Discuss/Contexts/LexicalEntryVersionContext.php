<?php

namespace App\Http\Discuss\Contexts;

use App\Adapters\BookAdapter;
use App\Helpers\LinkHelper;
use App\Http\Discuss\IDiscussContext;
use App\Models\Account;
use App\Models\Versioning\LexicalEntryVersion;
use App\Repositories\LexicalEntryRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LexicalEntryVersionContext implements IDiscussContext
{
    private LinkHelper $_linkHelper;

    private BookAdapter $_bookAdapter;

    private LexicalEntryRepository $_lexicalEntryRepository;

    public function __construct(LinkHelper $linkHelper, BookAdapter $bookAdapter, LexicalEntryRepository $lexicalEntryRepository)
    {
        $this->_linkHelper = $linkHelper;
        $this->_bookAdapter = $bookAdapter;
        $this->_lexicalEntryRepository = $lexicalEntryRepository;
    }

    public function resolve(Model $entity)
    {
        return $this->_linkHelper->lexicalEntryVersions($entity->id);
    }

    public function resolveById(int $entityId)
    {
        return LexicalEntryVersion::find($entityId);
    }

    public function available($entityOrId, ?Account $account = null)
    {
        return true;
    }

    public function getName(Model $entity)
    {
        if (! $entity) {
            return null;
        }

        $date = new Carbon($entity->created_at);

        return 'Lexical entry “'.$entity->word->word.'” by '.$entity->account->nickname.' created '.$date->diffForHumans();
    }

    public function getIconPath()
    {
        // Refer to Bootstrap glyphicons.
        return 'book';
    }

    public function view(Model $entity)
    {
        $gloss = $this->_lexicalEntryRepository->getLexicalEntry($entity->lexical_entry_id);
        if ($gloss->count() < 1) {
            return response(null, 404);
        }

        $model = $this->_bookAdapter->adaptLexicalEntries([$gloss->first()]);

        return view('discuss.context._gloss', $model);
    }
}
