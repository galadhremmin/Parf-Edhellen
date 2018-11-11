<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Adapters\SentenceAdapter;
use App\Repositories\SentenceRepository;
use DB;

class SentenceApiController extends Controller 
{
    private $_adapter;
    private $_repository;

    public function __construct(SentenceAdapter $adapter,
        SentenceRepository $repository)
    {
        $this->_adapter = $adapter;
        $this->_repository = $repository;
    }

    public function show(Request $request, int $id)
    {
        return $this->_repository->getSentence($id);
    }
}
