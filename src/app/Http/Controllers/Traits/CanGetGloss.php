<?php
namespace App\Http\Controllers\Traits;

use App\Adapters\BookAdapter;
use App\Repositories\{ForumRepository, GlossRepository};
use App\Models\Gloss;

trait CanGetGloss
{
    protected $_bookAdapter;
    protected $_forumRepository;
    protected $_glossRepository;

    public function __construct(BookAdapter $bookAdapter,
        ForumRepository $forumRepository,
        GlossRepository $glossRepository)
    {
        $this->_bookAdapter = $bookAdapter;
        $this->_forumRepository = $forumRepository;
        $this->_glossRepository = $glossRepository;
    }
    
    /**
     * Gets the gloss with the specified ID. The gloss is also adapted for the immediate
     * use as a view model.
     *
     * @param int $glossId
     * @param bool $coerceLatest
     * @return void
     */
    public function getGloss(int $glossId, bool $coerceLatest = false)
    {
        $glosses = $this->getGlossUnadapted($glossId, $coerceLatest);
        if ($glosses->count() < 1) {
            return null;
        }

        $gloss = $glosses->first();
        $comments = $this->_forumRepository->getCommentCountForEntities(Gloss::class, [$glossId]);
        return $this->_bookAdapter->adaptGlosses($glosses->toArray(), [/* no inflections */], $comments, $gloss->word);
    }

    /**
     * Gets the gloss with the specified ID. As there might be multiple translations
     * associated with the specified gloss, this method might return multiple glosses. 
     *
     * @param int $glossId
     * @param bool $coerceLatest
     * @return void
     */
    public function getGlossUnadapted(int $glossId, bool $coerceLatest = false)
    {
        $glosses = $this->_glossRepository->getGloss($glossId);
        if ($glosses->count() < 1) {
            return null;
        }

        $gloss = $glosses->first();
        if (! $gloss->is_latest && $coerceLatest) {
            $glossId = $this->_glossRepository->getLatestGloss($gloss->origin_gloss_id ?: $gloss->id);
            return $this->getGlossUnadapted($glossId, false);
        }

        return $glosses;
    }
}
