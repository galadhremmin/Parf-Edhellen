<?php

namespace App\Http\Discuss\Contexts;

use Illuminate\Database\Eloquent\Model;

use App\Adapters\SentenceAdapter;
use App\Models\Account;
use App\Http\Discuss\IDiscussContext;
use App\Helpers\LinkHelper;

class SentenceContext implements IDiscussContext
{
    private $_linkHelper;
    private $_sentenceAdapter;

    public function __construct(LinkHelper $linkHelper, SentenceAdapter $sentenceAdapter)
    {
        $this->_linkHelper      = $linkHelper;
        $this->_sentenceAdapter = $sentenceAdapter;
    }

    public function resolve(Model $entity)
    {
        if (! $entity) {
            return null;
        }

        return $this->_linkHelper->sentence($entity->language_id, $entity->language->name, 
            $entity->id, $entity->name);
    }

    function available($entityOrId, Account $account = null)
    {
        return true;
    }
    
    public function getName(Model $entity)
    {
        if (! $entity) {
            return null;
        }

        // Some sentences actually do lack an author (as they were imported). SO make sure one exists before adding 'by'.
        return 'Phrase “'.$entity->name.'”' . ($entity->account_id ? ' by '.$entity->account->nickname : '');
    }

    public function getIconPath()
    {
        // Refer to Bootstrap glyphicons.
        return 'align-justify';
    }

    public function view(Model $entity)
    {
        $data = $this->_sentenceAdapter->adaptFragments($entity->sentence_fragments, false);

        return view('discuss.context._sentence', [
            'sentence'     => $entity,
            'sentenceData' => $data
        ]);
    }
}
