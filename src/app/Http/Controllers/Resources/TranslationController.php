<?php

namespace App\Http\Controllers\Resources;

use App\Models\Translation;
use App\Adapters\SpeechAdapter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TranslationController extends Controller
{
    /*
    protected $_translationAdapter;

    public function __construct(SpeechAdapter $adapter) 
    {
        $this->_translationAdapter = $adapter;
    }
    */

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
        $translation = Translation::with('word', 'translation_group', 'sense', 'keywords')
            ->findOrFail($id);
        return view('translation.edit', ['translation' => $translation]);
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        $translation = new Translation;
        $translation->translation = $request->input('translation');
        $translation->save();

        return redirect()->route('translation.index');
    }

    public function update(Request $request, int $id)
    {
        $this->validateRequest($request, $id);

        $translation = Translation::findOrFail($id);
        $translation->translation = $request->input('translation');
        $translation->save();

        return redirect()->route('translation.index');
    } 

    public function destroy(Request $request, int $id) 
    {
        $speech = Translation::findOrFail($id);
        /*
        $speech->delete();

        return redirect()->route('speech.index');
        */
    }

    protected function validateRequest(Request $request, int $id = 0)
    {
        $this->validate($request, [
            'word' => 'required|min:1|max:32|unique:speeches,name'.($id === 0 ? '' : ','.$id.',id')
        ]);
    } 
}
