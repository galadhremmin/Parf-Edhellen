<?php
namespace App\Traits;

use App\Adapters\BookAdapter;
use App\Repositories\{ForumRepository, TranslationRepository};
use App\Models\ForumContext;

trait CanGetTranslationTrait 
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
    
    public function getTranslation(int $translationId)
    {
        $translation = $this->_translationRepository->getTranslation($translationId);
        if (! $translation) {
            return null;
        }

        $comments = $this->_forumRepository->getCommentCountForEntities(ForumContext::CONTEXT_TRANSLATION, [$translationId]);
        return $this->_bookAdapter->adaptTranslations([$translation], [/* no inflections */], $comments, $translation->word);
    }
}
