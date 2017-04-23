<?php

namespace App\Http\Controllers\Resources;

use App\Models\{ Inflection, Speech };
use App\Adapters\SpeechAdapter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InflectionController extends Controller
{
    public function index(Request $request)
    {
        $inflections = Inflection::all()->groupBy('Group')->sortBy('Name');
        return view('inflection.index', ['inflections' => $inflections]);
    }

    public function create(Request $request)
    {
        return view('inflection.create');
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
        $inflection->Name  = $request->input('name');
        $inflection->Group = $request->input('group');
        $inflection->save();

        return redirect()->route('inflection.index');
    }

    public function update(Request $request, int $id)
    {
        $this->validateRequest($request, $id);

        $inflection = Inflection::findOrFail($id);
        $inflection->Name  = $request->input('name');
        $inflection->Group = $request->input('group');
        $inflection->save();

        return redirect()->route('inflection.index');
    }

    public function destroy(Request $request, int $id) 
    {
        $inflection = Inflection::findOrFail($id);
        
        foreach ($inflection->sentenceFragmentAssociations as $association) {
            $association->delete();
        }

        $inflection->delete();

        return redirect()->route('inflection.index');
    }

    protected function validateRequest(Request $request, int $id = 0)
    {
        $rules = [
            'name'  => 'required|min:1|max:64|unique:inflection,Name'.($id === 0 ? '' : ','.$id.',InflectionID'),
            'group' => 'required|min:1|max:64'
        ];

        $this->validate($request, $rules);
    } 
}
