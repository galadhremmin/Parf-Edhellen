<?php

namespace App\Http\Discuss\Contexts;

use App\Adapters\BookAdapter;
use App\Helpers\LinkHelper;
use App\Http\Discuss\IDiscussContext;
use App\Models\Account;
use App\Models\Versioning\GlossVersion;
use App\Repositories\GlossRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class GlossVersionContext implements IDiscussContext
{
    private LinkHelper $_linkHelper;

    private BookAdapter $_bookAdapter;

    private GlossRepository $_glossRepository;

    public function __construct(LinkHelper $linkHelper, BookAdapter $bookAdapter, GlossRepository $glossRepository)
    {
        $this->_linkHelper = $linkHelper;
        $this->_bookAdapter = $bookAdapter;
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

        return 'Gloss “'.$entity->word->word.'” by '.$entity->account->nickname.' created '.$date->diffForHumans();
    }

    public function getIconPath()
    {
        // Refer to Bootstrap glyphicons.
        return 'book';
    }

    public function view(Model $entity)
    {
        $gloss = $this->_glossRepository->getGloss($entity->gloss_id);
        if ($gloss->count() < 1) {
            return response(null, 404);
        }

        $model = $this->_bookAdapter->adaptGlosses([$gloss->first()]);

        return view('discuss.context._gloss', $model);
    }
}
