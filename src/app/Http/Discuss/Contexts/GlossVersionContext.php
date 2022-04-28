<?php

namespace App\Http\Discuss\Contexts;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

use App\Adapters\BookAdapter;
use App\Repositories\GlossRepository;
use App\Http\Discuss\IDiscussContext;
use App\Models\{
    Account,
    Gloss
};
use App\Models\Versioning\GlossVersion;
use App\Helpers\LinkHelper;

class GlossVersionContext implements IDiscussContext
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

    public function resolveById(int $entityId)
    {
        return GlossVersion::find($entityId);
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
        
        $date = new Carbon($entity->created_at);
        return 'Gloss “'.$entity->word->word.'” by '.$entity->account->nickname.' created '.$date->diffForHumans();
    }

    public function getIconPath()
    {
        // Refer to Bootstrap glyphicons.
        return 'book';
    }

    public function view(Model $entity)
    {
        $gloss = Gloss::findOrFail($entity->gloss_id);
        $model = $this->_bookAdapter->adaptGlosses([$gloss]);
        return view('discuss.context._gloss', $model);
    }
}
