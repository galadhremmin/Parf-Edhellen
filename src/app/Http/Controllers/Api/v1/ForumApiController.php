<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Models\{ ForumPost };
use App\Http\Controllers\Controller;
use App\Helpers\StringHelper;

class ForumApiController extends Controller 
{
    public function __construct()
    {
    }

    /**
     * HTTP POST. Creates a new forum post.
     *
     * @param Request $request
     * @return response 201 on success
     */
    public function store(Request $request)
    {
        $this->validateRequest($request);

        // TODO

        return response($post, 201);
    }

    private function validateRequest(Request $request) 
    {
        $this->validate($request, [
            'content'    => 'required|string',
            'context_id' => 'required|exists:forum_contexts,id',
            'entity_id'  => 'required|numeric'
        ]);

        $entityId = intval($request->input('entity_id'));

        
    }
}