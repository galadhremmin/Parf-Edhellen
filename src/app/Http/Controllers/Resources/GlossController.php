<?php

namespace App\Http\Controllers\Resources;

use App\Adapters\BookAdapter;
use App\Http\Controllers\Abstracts\Controller;
use App\Http\Controllers\Traits\CanMapGloss;
use App\Http\Controllers\Traits\CanValidateGloss;
use App\Models\Gloss;
use App\Models\Language;
use App\Repositories\GlossRepository;
use Illuminate\Http\Request;

class GlossController extends Controller
{
    use CanMapGloss,
        CanValidateGloss;

    protected BookAdapter $_bookAdapter;

    protected GlossRepository $_glossRepository;

    public function __construct(BookAdapter $adapter, GlossRepository $glossRepository)
    {
        $this->_bookAdapter = $adapter;
        $this->_glossRepository = $glossRepository;
    }

    public function index(Request $request)
    {
        $latestGlosses = Gloss::notDeleted()
            ->orderBy('id', 'desc')
            ->take(10)
            ->with('word', 'account')
            ->get();

        $languages = Language::invented()
            ->orderBy('name')
            ->select('name', 'id')
            ->get();

        return view('admin.gloss.index', [
            'latestGlosses' => $latestGlosses,
            'languages' => $languages,
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

        return view('admin.gloss.list', [
            'language' => $language,
            'glosses' => $glosses,
        ]);
    }
}
