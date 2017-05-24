<?php

namespace App\Http\Controllers\Resources;

use App\Models\{ Translation, Keyword, Word, Language };
use App\Adapters\BookAdapter;
use App\Repositories\TranslationRepository;
use App\Helpers\{ LinkHelper, StringHelper };

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TranslationController extends Controller
{
    protected $_bookAdapter;
    protected $_translationRepository;

    public function __construct(BookAdapter $adapter, TranslationRepository $translationRepository) 
    {
        $this->_bookAdapter = $adapter;
        $this->_translationRepository = $translationRepository;
    }

    public function index(Request $request)
    {
        $latestTranslations = Translation::latest()
            ->notDeleted()
            ->notIndex()
            ->orderBy('id', 'desc')
            ->take(10)
            ->with('word', 'account')
            ->get();

        $languages = Language::invented()
            ->orderBy('name')
            ->select('name', 'id')
            ->get();

        return view('translation.index', [ 
            'latestTranslations' => $latestTranslations,
            'languages' => $languages
        ]);
    }

    public function listForLanguage(Request $request, int $id)
    {
        $language = Language::findOrFail($id);
        $translations = $this->_translationRepository->getTranslationListForLanguage($language->id);
        
        if (! $language->is_invented) {
            return redirect()->route('translation.index');
        }

        return view('translation.list', [
            'language' => $language,
            'translations' => $translations
        ]);
    }

    public function create(Request $request)
    {
        return view('translation.create');
    }

    public function edit(Request $request, int $id) 
    {
        // Eagerly load the translation.
        $translation = Translation::with('word', 'translation_group', 'sense', 'sense.word')
            ->findOrFail($id)
            ->getLatestVersion();

        if ($translation->id !== $id) {
            return redirect()->route('translation.edit', [
                'id' => $translation->id
            ]);
        }

        // Retrieve the words associated with the gloss' set of keywords. This is achieved by
        // joining with the _words_ table. The result is assigned to _keywords, which starts with
        // an underscore.
        $translation->_keywords = $translation->sense 
            ? $translation->sense
                ->keywords()
                ->join('words', 'words.id', 'keywords.word_id')
                ->where(function ($query) use($id) {
                    $query->whereNull('translation_id')
                        ->orWhere('translation_id', $id);
                })
                ->select('words.id', 'words.word')
                ->get()
            : [];

        return view('translation.edit', [
            'translation' => $translation
        ]);
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        $translation = new Translation;
        $translation = $this->saveTranslation($translation, $request);

        $link = new LinkHelper();
        return response([
            'id'  => $translation->id,
            'url' => $link->translation($translation->id)
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $this->validateRequest($request, $id);

        $translation = Translation::findOrFail($id);
        $translation = $this->saveTranslation($translation, $request);

        $link = new LinkHelper();
        return response([
            'id'  => $translation->id,
            'url' => $link->translation($translation->id)
        ], 200);
    } 

    public function destroy(Request $request, int $id) 
    {
        $this->validate($request, [
            'id'             => 'required|numeric|exists:translations,id',
            'replacement_id' => 'required|numeric|exists:translations,id'
        ]);

        $replacementId = intval($request->input('replacement_id'));
        $ok = $this->_translationRepository->deleteTranslationWithId($id, $replacementId);
        return $ok
            ? response(null, 204)
            : response(null, 500);
    }

    protected function saveTranslation(Translation $translation, Request $request)
    {
        $word  = $request->input('word');
        $sense = $request->input('sense.word.word');

        $translation->account_id   = intval($request->input('account_id'));
        $translation->language_id  = intval($request->input('language_id'));
        $translation->speech_id    = intval($request->input('speech_id'));

        $translation->is_rejected  = boolval($request->input('is_rejected'));
        $translation->is_uncertain = boolval($request->input('is_uncertain'));
        $translation->is_latest    = 1;
            
        $translation->translation  = $request->input('translation');
        $translation->source       = $request->input('source');
        $translation->comments     = $request->input('comments');

        $translation->translation_group_id = $request->has('translation_group_id') 
            ? intval($request->input('translation_group_id'))
            : null;

        $translation->tengwar  = $request->has('tengwar')
            ? $request->input('tengwar')
            : null;

        $keywords = array_map(function ($k) {
            return StringHelper::toLower($k['word']);
        }, $request->input('keywords'));

        return $this->_translationRepository->saveTranslation($word, $sense, $translation, $keywords);
    }

    protected function validateRequest(Request $request, $id = 0)
    {
        $this->validate($request, [
            'id'              => 'sometimes|required|numeric|exists:translations,id',
            'account_id'      => 'required|numeric|exists:accounts,id',
            'language_id'     => 'required|numeric|exists:languages,id',
            'speech_id'       => 'required|numeric|exists:speeches,id',
            'word'            => 'required|string|min:1|max:64',
            'sense.word.word' => 'required|string|min:1|max:64',
            'translation'     => 'required|string|min:1|max:255',
            'source'          => 'required|string|min:3',
            'is_rejected'     => 'required|boolean',
            'is_uncertain'    => 'required|boolean',
            'keywords'        => 'sometimes|array',
            'keywords.*.word' => 'sometimes|string|min:1|max:64',

            'translation_group_id' => 'sometimes|numeric|exists:translation_groups,id',
            'tengwar'              => 'sometimes|string|min:1|max:128'
        ]);
    } 
}
