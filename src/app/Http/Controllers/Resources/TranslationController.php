<?php

namespace App\Http\Controllers\Resources;

use App\Models\Translation;
use App\Adapters\BookAdapter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TranslationController extends Controller
{
    protected $_bookAdapter;

    public function __construct(BookAdapter $adapter) 
    {
        $this->_bookAdapter = $adapter;
    }

    public function index(Request $request)
    {
        return view('translation.index');
    }

    public function create(Request $request)
    {
        return view('translation.create');
    }

    public function edit(Request $request, int $id) 
    {
        // Eagerly load the translation.
        $translation = Translation::with('word', 'translation_group', 'sense', 'sense.word')
            ->findOrFail($id);

        // Retrieve the words associated with the gloss' set of keywords. This is achieved by
        // joining with the _words_ table. The result is assigned to _keywords, which starts with
        // an underscore.
        $translation->_keywords = $translation->sense
            ->keywords()
            ->join('words', 'words.id', 'keywords.word_id')
            ->select('words.id', 'words.word')
            ->get();

        return view('translation.edit', [
            'translation' => $translation
        ]);
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        /*
        $translation = new Translation;
        $translation->translation = $request->input('translation');
        $translation->save();
        */

        return response(null, 201);
    }

    public function update(Request $request, int $id)
    {
        $this->validateRequest($request, $id);

        /*
        $translation = Translation::findOrFail($id);
        $translation->translation = $request->input('translation');
        $translation->save();
        */

        return response(null, 200);
    } 

    public function destroy(Request $request, int $id) 
    {
        $speech = Translation::findOrFail($id);
        /*
        $speech->delete();

        return redirect()->route('speech.index');
        */
    }

    protected function validateRequest(Request $request, $id = 0)
    {
        $this->validate($request, [
            'id'              => 'sometimes|required|numeric|exists:translations,id',
            'account_id'      => 'required|numeric|exists:accounts,id',
            'language_id'     => 'required|numeric|exists:languages,id',
            'word'            => 'required|string|min:1|max:64',
            'translation'     => 'required|string|min:1|max:255',
            'source'          => 'required|string|min:3',
            'is_uncertain'    => 'required|boolean',
            'is_rejected'     => 'required|boolean',
            'keywords'        => 'sometimes|array',
            'keywords.*.word' => 'sometimes|string|min:1|max:64',

            'translation_group_id' => 'sometimes|numeric|exists:translation_groups,id'
        ]);
    } 
}
