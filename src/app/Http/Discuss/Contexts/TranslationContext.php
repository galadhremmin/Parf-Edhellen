<?php

namespace App\Http\Discuss\Contexts;

use Illuminate\Database\Eloquent\Model;

use App\Adapters\BookAdapter;
use App\Repositories\TranslationRepository;
use App\Models\Account;
use App\Http\Discuss\IDiscussContext;
use App\Helpers\LinkHelper;

class TranslationContext implements IDiscussContext
{
    private $_linkHelper;
    private $_bookAdapter;
    private $_translationRepository;

    public function __construct(LinkHelper $linkHelper, BookAdapter $bookAdapter, TranslationRepository $translationRepository)
    {
        $this->_linkHelper            = $linkHelper;
        $this->_bookAdapter           = $bookAdapter; 
        $this->_translationRepository = $translationRepository;
    }

    public function resolve(Model $entity)
    {
        return $this->_linkHelper->translationVersions($entity->id);
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
        
        return 'Gloss “'.$entity->word->word.'” by '.$entity->account->nickname;
    }

    public function getIconPath()
    {
        // Refer to Bootstrap glyphicons.
        return 'book';
    }

    public function view(Model $entity)
    {
        $data = $this->_translationRepository->getTranslation($entity->id);
        $model = $this->_bookAdapter->adaptTranslations([$data]);

        return view('discuss.context._translation', $model);
    }
}
