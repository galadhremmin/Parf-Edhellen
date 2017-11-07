<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Adapters\BookAdapter;
use App\Repositories\GlossRepository;
use App\Models\{
    Gloss, 
    Keyword, 
    Word, 
    Language 
};
use App\Helpers\{ 
    LinkHelper, 
    StringHelper 
};
use App\Http\Controllers\Traits\{
    CanValidateGloss, 
    CanMapGloss 
};
use App\Events\{
    GlossDestroyed
};

class GlossController extends Controller
{
    use CanMapGloss,
        CanValidateGloss;

    protected $_bookAdapter;
    protected $_glossRepository;

    public function __construct(BookAdapter $adapter, GlossRepository $glossRepository) 
    {
        $this->_bookAdapter = $adapter;
        $this->_glossRepository = $glossRepository;
    }

    public function index(Request $request)
    {
        $latestGlosses = Gloss::latest()
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

        return view('gloss.index', [ 
            'latestGlosses' => $latestGlosses,
            'languages' => $languages
        ]);
    }

    public function listForLanguage(Request $request, int $id)
    {
        $language = Language::findOrFail($id);
        $glosses = Gloss::active()
            ->where('language_id', $id)
            ->join('words', 'words.id', 'glosses.word_id')
            ->orderBy('words.word', 'asc')
            ->with('translations', 'account', 'sense.word', 'speech', 'keywords', 'word')
            ->select('glosses.*')
            ->paginate(30);
        return view('gloss.list', [
            'language' => $language,
            'glosses' => $glosses
        ]);
    }

    public function create(Request $request)
    {
        return view('gloss.create');
    }

    public function edit(Request $request, int $id) 
    {
        // Eagerly load the gloss.
        $gloss = Gloss::with('word', 'gloss_group', 'sense', 'sense.word', 'translations')
            ->findOrFail($id)
            ->getLatestVersion();

        if ($gloss->id !== $id) {
            return redirect()->route('gloss.edit', [
                'id' => $gloss->id
            ]);
        }

        // Retrieve the words associated with the gloss' set of keywords. This is achieved by
        // joining with the _words_ table. The result is assigned to _keywords, which starts with
        // an underscore.
        $gloss->_keywords = $this->_glossRepository->getKeywords($gloss->sense_id, $gloss->id);

        return $request->ajax() 
            ? $gloss
            : view('gloss.edit', [
                'gloss' => $gloss
            ]);
    }

    public function store(Request $request)
    {
        $this->validateGlossInRequest($request);

        $gloss = new Gloss;
        $gloss = $this->saveGloss($gloss, $request);

        $link = new LinkHelper();
        return response([
            'id'  => $gloss->id,
            'url' => $link->gloss($gloss->id)
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $this->validateGlossInRequest($request, $id);

        $gloss = Gloss::findOrFail($id);
        $gloss = $this->saveGloss($gloss, $request);

        $link = new LinkHelper();
        return response([
            'id'  => $gloss->id,
            'url' => $link->gloss($gloss->id)
        ], 200);
    } 

    public function confirmDelete(Request $request, int $id)
    {
        $gloss = Gloss::findOrFail($id);
        
        return view('gloss.confirm-delete', [
            'gloss' => $gloss
        ]);
    }

    public function destroy(Request $request, int $id) 
    {
        $this->validate($request, [
            'replacement_id' => 'sometimes|numeric|exists:glosses,id'
        ]);

        $gloss = Gloss::findOrFail($id);
        $replacementId = $request->has('replacement_id') 
            ? intval($request->input('replacement_id'))
            : null;
        $replacement = $replacementId !== null 
            ? Gloss::findOrFail($replacementId)
            : null;

        $ok = $this->_glossRepository->deleteGlossWithId($id, $replacementId);
        if ($ok) {
            event(new GlossDestroyed($gloss, $replacement));
        }

        return $ok
            ? response(null, 204)
            : response(null, 400);
    }

    protected function saveGloss(Gloss $gloss, Request $request)
    {
        $map = $this->mapGloss($gloss, $request);
        extract($map);

        return $this->_glossRepository->saveGloss($word, $sense, $gloss, $translations, $keywords);
    }
}
