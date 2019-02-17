<?php

namespace App\Http\Discuss\Contexts;

use Illuminate\Database\Eloquent\Model;

use App\Http\Discuss\IDiscussContext;
use App\Helpers\LinkHelper;
use App\Models\{
    Account,
    Sentence
};
use App\Repositories\SentenceRepository;

class SentenceContext implements IDiscussContext
{
    private $_linkHelper;
    private $_repository;

    public function __construct(LinkHelper $linkHelper, SentenceRepository $repository)
    {
        $this->_linkHelper = $linkHelper;
        $this->_repository = $repository;
    }

    public function resolve(Model $entity)
    {
        if (! $entity) {
            return null;
        }

        return $this->_linkHelper->sentence($entity->language_id, $entity->language->name, 
            $entity->id, $entity->name);
    }

    public function resolveById(int $entityId, Account $account = null)
    {
        return Sentence::find($entityId);
    }

    public function available($entityOrId, Account $account = null)
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
        $sentence = $this->_repository->getSentence($entity->id);
        return view('discuss.context._sentence', [
            'sentence' => $sentence
        ]);
    }
}
