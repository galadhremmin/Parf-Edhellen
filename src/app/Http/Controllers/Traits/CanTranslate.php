<?php
namespace App\Http\Controllers\Traits;

use App\Adapters\BookAdapter;
use App\Repositories\{
    ForumRepository, 
    SentenceRepository, 
    GlossRepository
};
use App\Models\Gloss;

trait CanTranslate
{
    protected $_bookAdapter;
    protected $_forumRepository;
    protected $_glossRepository;
    protected $_sentenceRepository;

    public function __construct(BookAdapter $bookAdapter,
        ForumRepository $forumRepository,
        GlossRepository $glossRepository, 
        SentenceRepository $sentenceRepository)
    {
        $this->_bookAdapter = $bookAdapter;
        $this->_forumRepository = $forumRepository;
        $this->_glossRepository = $glossRepository;
        $this->_sentenceRepository = $sentenceRepository;
    }

    public function translate(string $word, int $languageId = 0, bool $includeInflections = true, bool $includeOld = true)
    {
        $glosses = $this->_glossRepository->getWordGlosses($word, $languageId, $includeOld);
        $glossIds = array_map(function ($v) {
            return $v->id;
        }, $glosses);

        $inflections = $includeInflections
            ? $this->_sentenceRepository->getInflectionsForGlosses($glossIds) 
            : [];
        $comments = $this->_forumRepository->getCommentCountForEntities(Gloss::class, $glossIds);
        
        return $this->_bookAdapter->adaptGlosses($glosses, $inflections, $comments, $word);
    }
}
