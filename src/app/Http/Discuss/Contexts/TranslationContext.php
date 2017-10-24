<?php

namespace App\Http\Discuss\Contexts;

use Illuminate\Database\Eloquent\Model;

use App\Models\Account;
use App\Http\Discuss\IDiscussContext;
use App\Helpers\LinkHelper;

class TranslationContext implements IDiscussContext
{
    private $_linkHelper;

    public function __construct(LinkHelper $linkHelper)
    {
        $this->_linkHelper = $linkHelper;
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
}
