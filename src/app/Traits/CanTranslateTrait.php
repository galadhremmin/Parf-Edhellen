<?php
namespace App\Traits;

use App\Adapters\BookAdapter;
use App\Repositories\{ForumRepository, SentenceRepository, TranslationRepository};
use App\Models\ForumContext;

trait CanTranslateTrait 
{
    protected $_bookAdapter;
    protected $_forumRepository;
    protected $_translationRepository;
    protected $_sentenceRepository;

    public function __construct(BookAdapter $bookAdapter,
        ForumRepository $forumRepository,
        TranslationRepository $translationRepository, 
        SentenceRepository $sentenceRepository)
    {
        $this->_bookAdapter = $bookAdapter;
        $this->_forumRepository = $forumRepository;
        $this->_translationRepository = $translationRepository;
        $this->_sentenceRepository = $sentenceRepository;
    }

    public function translate(string $word, int $languageId = 0, bool $includeInflections = true, bool $includeOld = true)
    {
        $translations = $this->_translationRepository->getWordTranslations($word, $languageId, $includeOld);
        $translationIds = array_map(function ($v) {
                return $v->id;
            }, $translations);

        $inflections = $includeInflections
            ? $this->_sentenceRepository->getInflectionsForTranslations($translationIds) 
            : [];
        $comments = $this->_forumRepository->getCommentCountForEntities(ForumContext::CONTEXT_TRANSLATION, $translationIds);
        
        return $this->_bookAdapter->adaptTranslations($translations, $inflections, $comments, $word);
    }
}
