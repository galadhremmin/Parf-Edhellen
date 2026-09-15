<?php

namespace App\Http\Controllers\Resources;

use App\Adapters\BookAdapter;
use App\Http\Controllers\Abstracts\Controller;
use App\Http\Controllers\Traits\CanMapGloss;
use App\Http\Controllers\Traits\CanValidateGloss;
use App\Models\Language;
use App\Models\LexicalEntry;
use App\Models\Speech;
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
        $filters = $request->validate([
            'word' => 'nullable|string|max:128',
            'gloss' => 'nullable|string|max:128',
            'sense' => 'nullable|string|max:128',
            'speech_id' => 'nullable|integer',
            'missing' => 'nullable|in:source,sense',
        ]);

        $language = Language::findOrFail($id);
        $lexicalEntries = $this->_lexicalEntryRepository->getLexicalEntriesForLanguage($id, $filters);
        $speeches = Speech::orderBy('name')
            ->select('id', 'name')
            ->get();

        return view('admin.gloss.list', [
            'language' => $language,
            'glosses' => $lexicalEntries,
            'filters' => $filters,
            'speeches' => $speeches,
        ]);
    }

    public function updateSense(Request $request, int $id)
    {
        $data = $request->validate([
            'sense' => 'required|string|max:255',
        ]);

        $lexicalEntry = LexicalEntry::active()->findOrFail($id);
        $this->_lexicalEntryRepository->saveSense($lexicalEntry, trim($data['sense']));

        return redirect()->to(url()->previous().'#lexical-entry-'.$lexicalEntry->id)
            ->with('updated_sense_id', $lexicalEntry->id);
    }
}
