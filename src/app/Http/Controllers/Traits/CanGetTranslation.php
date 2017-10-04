<?php
namespace App\Http\Controllers\Traits;

use App\Adapters\BookAdapter;
use App\Repositories\{ForumRepository, TranslationRepository};
use App\Models\ForumContext;

trait CanGetTranslation
{
    protected $_bookAdapter;
    protected $_forumRepository;
    protected $_translationRepository;

    public function __construct(BookAdapter $bookAdapter,
        ForumRepository $forumRepository,
        TranslationRepository $translationRepository)
    {
        $this->_bookAdapter = $bookAdapter;
        $this->_forumRepository = $forumRepository;
        $this->_translationRepository = $translationRepository;
    }
    
    public function getTranslation(int $translationId, bool $coerceLatest = false)
    {
        $translation = $this->getTranslationUnadapted($translationId, $coerceLatest);
        if (! $translation) {
            return null;
        }

        $comments = $this->_forumRepository->getCommentCountForEntities(ForumContext::CONTEXT_TRANSLATION, [$translationId]);
        return $this->_bookAdapter->adaptTranslations([$translation], [/* no inflections */], $comments, $translation->word);
    }

    public function getTranslationUnadapted(int $translationId, bool $coerceLatest = false)
    {
        $translation = $this->_translationRepository->getTranslation($translationId);
        if (! $translation) {
            return null;
        }

        if (! $translation->is_latest && $coerceLatest) {
            $translationId = $this->_translationRepository->getLatestTranslation($translation->origin_translation_id ?: $translation->id);
            return $this->getTranslationUnadapted($translationId, false);
        }

        return $translation;
    }
}
