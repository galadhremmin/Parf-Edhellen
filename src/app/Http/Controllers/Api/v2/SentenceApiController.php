<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Adapters\SentenceAdapter;
use App\Models\Sentence;

class SentenceApiController extends Controller 
{
    private $_adapter;

    public function __construct(SentenceAdapter $adapter)
    {
        $this->_adapter = $adapter;
    }

    public function show(Request $request, int $id)
    {
        $sentence = Sentence::findOrFail($id);
        $data = $this->_adapter->adaptFragments($sentence->sentence_fragments);
        return $data;
    }
}
