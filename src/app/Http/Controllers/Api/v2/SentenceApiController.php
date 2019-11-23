<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Repositories\SentenceRepository;
use DB;

class SentenceApiController extends Controller 
{
    private $_repository;

    public function __construct(SentenceRepository $repository)
    {
        $this->_repository = $repository;
    }

    public function show(Request $request, int $id)
    {
        $sentence = $this->_repository->getSentence($id);
        return $sentence;
    }
}
