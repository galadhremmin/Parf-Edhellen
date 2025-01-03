<?php

namespace App\Http\Discuss\Contexts;

use App\Helpers\LinkHelper;
use App\Http\Discuss\IDiscussContext;
use App\Models\Account;
use App\Models\Sentence;
use App\Repositories\SentenceRepository;
use Illuminate\Database\Eloquent\Model;

class SentenceContext implements IDiscussContext
{
    private LinkHelper $_linkHelper;

    private SentenceRepository $_repository;

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

    public function resolveById(int $entityId)
    {
        return Sentence::find($entityId);
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

        // Some sentences actually do lack an author (as they were imported). SO make sure one exists before adding 'by'.
        return 'Phrase “'.$entity->name.'”'.($entity->account_id ? ' by '.$entity->account->nickname : '');
    }

    public function getIconPath()
    {
        // Refer to Bootstrap glyphicons.
        return 'book';
    }

    public function view(Model $entity)
    {
        $sentence = $this->_repository->getSentence($entity->id);
        if (! $sentence) {
            return response(null, 404);
        }

        return view('discuss.context._sentence', [
            'sentence' => $sentence,
        ]);
    }
}
