<?php

namespace App\Http\Discuss\Contexts;

use Illuminate\Database\Eloquent\Model;

use App\Adapters\BookAdapter;
use App\Repositories\GlossRepository;
use App\Models\Account;
use App\Http\Discuss\IDiscussContext;
use App\Helpers\LinkHelper;

class GlossContext implements IDiscussContext
{
    private $_linkHelper;
    private $_bookAdapter;
    private $_glossRepository;

    public function __construct(LinkHelper $linkHelper, BookAdapter $bookAdapter, GlossRepository $glossRepository)
    {
        $this->_linkHelper      = $linkHelper;
        $this->_bookAdapter     = $bookAdapter; 
        $this->_glossRepository = $glossRepository;
    }

    public function resolve(Model $entity)
    {
        return $this->_linkHelper->glossVersions($entity->id);
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
        $data = $this->_glossRepository->getGloss($entity->id);
        $model = $this->_bookAdapter->adaptGlosses([$data]);

        return view('discuss.context._gloss', $model);
    }
}
