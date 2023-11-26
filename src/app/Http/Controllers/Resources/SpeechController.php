<?php

namespace App\Http\Controllers\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Speech;
use App\Http\Controllers\Abstracts\Controller;
use App\Events\{
    SpeechDestroyed
};

class SpeechController extends Controller
{
    public function index(Request $request)
    {
        $speeches = Speech::all()->sortBy('name');
        return view('admin.speech.index', ['speeches' => $speeches]);
    }

    public function create(Request $request)
    {
        return view('admin.speech.create');
    }

    public function edit(Request $request, int $id) 
    {
        $speech = Speech::findOrFail($id);
        return view('admin.speech.edit', ['speech' => $speech]);
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
        $speech->name = $request->input('name');
        
        $speech->save();

        return redirect()->route('speech.index');
    } 

    public function destroy(Request $request, int $id) 
    {
        $speech = Speech::findOrFail($id);

        if ($speech->sentence_fragments()->count() > 0) {
            foreach ($speech->sentence_fragments as $fragment) {
                $fragment->speech_id = null;
                $fragment->save();
            }
        }

        $speech->delete();

        event(new SpeechDestroyed($speech));

        return redirect()->route('speech.index');
    }

    protected function validateRequest(Request $request, int $id = 0)
    {
        $this->validate($request, [
            'name' => 'required|min:1|max:32|unique:speeches,name'.($id === 0 ? '' : ','.$id.',id')
        ]);
    } 
}
