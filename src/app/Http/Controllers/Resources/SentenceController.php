<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\Sentence;
use App\Repositories\SentenceRepository;
use Illuminate\Http\Request;

class SentenceController extends Controller
{
    protected SentenceRepository $_sentenceRepository;

    public function __construct(SentenceRepository $sentenceRepository)
    {
        $this->_sentenceRepository = $sentenceRepository;
    }

    public function index(Request $request)
    {
        $sentences = $this->_sentenceRepository->getAllGroupedByLanguage();

        return view('admin.sentence.index', ['sentences' => $sentences]);
    }

    public function confirmDestroy(Request $request, int $id)
    {
        $sentence = Sentence::findOrFail($id);

        return view('admin.sentence.confirm-destroy', [
            'sentence' => $sentence,
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $sentence = Sentence::findOrFail($id);
        $this->_sentenceRepository->destroy($sentence);

        return redirect()->route('sentence.index');
    }
}
