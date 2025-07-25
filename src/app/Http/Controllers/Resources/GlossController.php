<?php

namespace App\Http\Controllers\Resources;

use App\Adapters\BookAdapter;
use App\Http\Controllers\Abstracts\Controller;
use App\Http\Controllers\Traits\CanMapGloss;
use App\Http\Controllers\Traits\CanValidateGloss;
use App\Models\LexicalEntry;
use App\Models\Language;
use App\Repositories\LexicalEntryRepository;
use Illuminate\Http\Request;

class GlossController extends Controller
{
    use CanMapGloss,
        CanValidateGloss;

    protected BookAdapter $_bookAdapter;

    protected LexicalEntryRepository $_lexicalEntryRepository;

    public function __construct(BookAdapter $adapter, LexicalEntryRepository $lexicalEntryRepository)
    {
        $this->_bookAdapter = $adapter;
        $this->_lexicalEntryRepository = $lexicalEntryRepository;
    }

    public function index(Request $request)
    {
        $latestLexicalEntries = LexicalEntry::notDeleted()
            ->orderBy('id', 'desc')
            ->take(10)
            ->with('word', 'account')
            ->get();

        $languages = Language::invented()
            ->orderBy('name')
            ->select('name', 'id')
            ->get();

        return view('admin.gloss.index', [
            'latestGlosses' => $latestLexicalEntries,
            'languages' => $languages,
        ]);
    }

    public function listForLanguage(Request $request, int $id)
    {
        $language = Language::findOrFail($id);
        $lexicalEntries = LexicalEntry::active()
            ->where('language_id', $id)
            ->join('words', 'words.id', 'lexical_entries.word_id')
            ->orderBy('words.word', 'asc')
            ->with('glosses', 'account', 'sense.word', 'speech', 'keywords', 'word')
            ->select('lexical_entries.*')
            ->paginate(30);

        return view('admin.gloss.list', [
            'language' => $language,
            'glosses' => $lexicalEntries,
        ]);
    }
}
