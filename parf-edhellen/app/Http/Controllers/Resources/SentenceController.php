<?php

namespace App\Http\Controllers\Resources;

use App\Models\Sentence;
use App\Repositories\SentenceRepository;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SentenceController extends Controller
{
    protected $_sentenceRepository;

    public function __construct(SentenceRepository $sentenceRepository)
    {
        $this->_sentenceRepository = $sentenceRepository;
    }

    public function index(Request $request)
    {
        $sentences = Sentence::all();
        return view('sentence.index', ['sentences' => $sentences]);
    }

    public function create(Request $request)
    {
        return view('sentence.create');
    }

    public function edit(Request $request, int $id) 
    {
        $sentence = Sentence::findOrFail($id);
        return view('sentence.edit', ['sentence' => $sentence]);
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);
        
        $inflection = new Inflection;
        $inflection->Name  = $request->input('name');
        $inflection->Group = $request->input('group');
        $inflection->save();

        return redirect()->route('sentence.index');
    }

    public function update(Request $request, int $id)
    {
        $this->validateRequest($request, $id);

        $sentence = Sentence::findOrFail($id);
        $sentence->Name  = $request->input('name');
        $sentence->save();

        return redirect()->route('sentence.index');
    }

    public function destroy(Request $request, int $id) 
    {
        $sentence = Sentence::findOrFail($id);
        
        foreach ($sentence->fragments as $fragment) {
            $fragment->delete();
        }

        $sentence->delete();

        return redirect()->route('sentence.index');
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
