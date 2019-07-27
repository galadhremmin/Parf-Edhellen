<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Adapters\BookAdapter;
use App\Repositories\GlossRepository;
use App\Models\{
    Gloss, 
    Language 
};
use App\Http\Controllers\Traits\{
    CanValidateGloss, 
    CanMapGloss 
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
        $gloss = Gloss::with(
            'account', 'word', 'gloss_details', 'gloss_group',
            'sense', 'sense.word', 'translations')
            ->findOrFail($id)
            ->getLatestVersion();

        if ($gloss->id !== $id) {
            return redirect()->route('gloss.edit', [
                'id' => $gloss->id
            ]);
        }

        // Overwrite the entity's keywords with a complete list of keywords (including keywords associated with the sense).
        $gloss->keywords = $this->_glossRepository->getKeywords($gloss->sense_id, $gloss->id);

        return $request->ajax() 
            ? $gloss
            : view('gloss.edit', [
                'gloss' => $gloss
            ]);
    }

    public function confirmDelete(Request $request, int $id)
    {
        $gloss = Gloss::findOrFail($id);
        
        return view('gloss.confirm-delete', [
            'gloss' => $gloss
        ]);
    }
}
