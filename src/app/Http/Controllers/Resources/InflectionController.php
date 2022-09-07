<?php

namespace App\Http\Controllers\Resources;

use Illuminate\Http\Request;

use App\Http\Controllers\Abstracts\Controller;
use App\Events\{
    InflectionDestroyed
};
use App\Models\{ 
    Inflection, 
    Speech 
};

class InflectionController extends Controller
{
    public function index(Request $request)
    {
        $inflections = Inflection::all()->groupBy('group_name')->sortBy('name');
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
        $inflection->name       = $request->input('name');
        $inflection->group_name = $request->input('group');
        $inflection->save();

        return redirect()->route('inflection.index');
    }

    public function update(Request $request, int $id)
    {
        $this->validateRequest($request, $id);

        $inflection = Inflection::findOrFail($id);
        $inflection->name       = $request->input('name');
        $inflection->group_name = $request->input('group');
        $inflection->save();

        return redirect()->route('inflection.index');
    }

    /*
    20220828: Disabled destroying inflections as the deletion process is non-trivial and requires 
              a deletion flow where you have to provide an alternative inflection.

    public function destroy(Request $request, int $id) 
    {
        $inflection = Inflection::findOrFail($id);
        
        foreach ($inflection->sentence_fragment_associations as $association) {
            $association->delete();
        }

        $inflection->delete();

        event(new InflectionDestroyed($inflection));
        
        return redirect()->route('inflection.index');
    }
    */

    protected function validateRequest(Request $request, int $id = 0)
    {
        $rules = [
            'name'  => 'required|min:1|max:64|unique:inflections,name'.($id === 0 ? '' : ','.$id.',id'),
            'group' => 'required|min:1|max:64'
        ];

        $this->validate($request, $rules);
    } 
}
