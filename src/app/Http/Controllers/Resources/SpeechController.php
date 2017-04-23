<?php

namespace App\Http\Controllers\Resources;

use App\Models\Speech;
use App\Adapters\SpeechAdapter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpeechController extends Controller
{
    protected $_speechAdapter;

    public function __construct(SpeechAdapter $adapter) 
    {
        $this->_speechAdapter = $adapter;
    }

    public function index(Request $request)
    {
        $speeches = Speech::all()->sortBy('name');
        return view('speech.index', ['speeches' => $speeches]);
    }

    public function create(Request $request)
    {
        return view('speech.create');
    }

    public function edit(Request $request, int $id) 
    {
        $speech = Speech::findOrFail($id);
        return view('speech.edit', ['speech' => $speech]);
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        $speech = new Speech;
        $speech->name = $request->input('name');
        $speech->save();

        return redirect()->route('speech.index');
    }

    public function update(Request $request, int $id)
    {
        $this->validateRequest($request, $id);

        $speech = Speech::findOrFail($id);
        $speech->Name = $request->input('name');
        
        $speech->save();

        return redirect()->route('speech.index');
    } 

    public function destroy(Request $request, int $id) 
    {
        $speech = Speech::findOrFail($id);

        foreach ($speech->sentenceFragments as $fragment) {
            $fragment->speech_id = null;
            $fragment->save();
        }

        $speech->delete();

        return redirect()->route('speech.index');
    }

    protected function validateRequest(Request $request, int $id = 0)
    {
        $this->validate($request, [
            'name' => 'required|min:1|max:32|unique:speech,name'.($id === 0 ? '' : ','.$id.',id')
        ]);
    } 
}
