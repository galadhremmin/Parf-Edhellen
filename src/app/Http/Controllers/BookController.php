<?php

namespace App\Http\Controllers;

use App\Models\ForumContext;
use App\Adapters\BookAdapter;
use App\Repositories\{ForumRepository, TranslationRepository};

use Illuminate\Http\Request;

class BookController extends Controller
{
    protected $_forumRepository;
    protected $_translationRepository;
    protected $_adapter;

    public function __construct(ForumRepository $forumRepository, TranslationRepository $translationRepository, BookAdapter $adapter)
    {
        $this->_forumRepository = $forumRepository;
        $this->_translationRepository = $translationRepository;
        $this->_adapter = $adapter;
    }

    public function pageForWord(Request $request, string $word)
    {
        $translations = $this->_translationRepository->getWordTranslations($word);
        
        $translationIds = array_map(function ($t) {
            return $t->id;
        }, $translations);
        $comments = $this->_forumRepository->getCommentCountForEntities(ForumContext::CONTEXT_TRANSLATION, $translationIds);

        $model = $this->_adapter->adaptTranslations($translations, [], $comments, $word, true, false);
        return view('book.page', $model);
    }

    public function pageForTranslationId(Request $request, int $id)
    {
        $translation = $this->_translationRepository->getTranslation($id);
        $comments = $this->_forumRepository->getCommentCountForEntities(ForumContext::CONTEXT_TRANSLATION, [$id]);

        $model = $this->_adapter->adaptTranslations([ $translation ], [], $comments, $translation->word, true, false);
        return view('book.page', $model);
    }

    public function versions(Request $request, int $id)
    {
        $translations = $this->_translationRepository->getVersions($id);
        $model = $this->_adapter->adaptTranslations($translations, [], [], null, false, false);

        return view('book.version', [
            'word'      => $translations[0]->word,
            'versions'  => $model['sections'][0]['glosses']
        ]);
    }

}
