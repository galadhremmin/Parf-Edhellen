<?php

namespace App\Http\Controllers;

use App\Models\{ Inflection, Speech };
use App\Adapters\SpeechAdapter;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InflectionController extends Controller
{
    public function create(Request $request)
    {
        $speechId = intval($request->input('speech'));
        $speech   = Speech::findOrFail($speechId);
        return view('inflection.create', [ 'speech' => $speech ]);
    }

    public function edit(Request $request, int $id) 
    {
        $inflection = Inflection::findOrFail($id);
        return view('inflection.edit', ['inflection' => $inflection]);
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);
        
        $inflection = new Inflection;
        $inflection->SpeechID = intval($request->input('speechId'));
        $inflection->Name = $request->input('name');
        $inflection->save();

        return redirect()->route('speech.edit', [ 'id' => $inflection->SpeechID ]);
    }

    public function update(Request $request, int $id)
    {
        $this->validateRequest($request, $id);

        $inflection = Inflection::findOrFail($id);
        $inflection->Name = $request->input('name');
        $inflection->save();

        return redirect()->route('speech.edit', [ 'id' => $inflection->SpeechID ]);
    }

    public function destroy(Request $request, int $id) 
    {
        $inflection = Inflection::findOrFail($id);
        $speechId = $inflection->SpeechID;
        
        foreach ($inflection->sentenceFragments as $fragment) {
            $fragment->InflectionID = null;
            $fragment->save();
        }

        $inflection->delete();

        return redirect()->route('speech.edit', [ 'id' => $speechId ]);
    }

    protected function validateRequest(Request $request, int $id = 0)
    {
        $rules = [
            'name' => 'required|min:1|max:32|unique:inflection,Name'.($id === 0 ? '' : ','.$id.',InflectionID')
        ];

        // New entities must have a valid speech
        if ($id === 0) {
            $rules['speechId'] = 'required|numeric|exists:speech,SpeechID';
        }

        $this->validate($request, $rules);
    } 
}
