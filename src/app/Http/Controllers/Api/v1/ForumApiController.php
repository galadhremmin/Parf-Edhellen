<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Models\{ ForumPost, ForumContext, Translation, Sentence };
use App\Http\Controllers\Controller;
use App\Helpers\StringHelper;

class ForumApiController extends Controller 
{
    public function __construct()
    {
    }

    public function index(Request $request)
    {
        $data = $this->getEntity($request);
        if (! $data) {
            return response(null, 404);
        }

        return ForumPost::where('context_id', $data['id'])
            ->where('entity_id', $data['entity']->id)
            ->get();
    }

    /**
     * HTTP POST. Creates a new forum post.
     *
     * @param Request $request
     * @return response 201 on success
     */
    public function store(Request $request)
    {
        $data = $this->getEntity($request);

        return response($data, 201);
    }

    private function getEntity(Request $request) 
    {
        $this->validate($request, [
            'context'   => 'required|exists:forum_contexts,name',
            'entity_id' => 'required|numeric'
        ]);

        // Retrieve the context
        $context = ForumContext::where('name', $request->input('context'))
            ->select('id')
            ->firstOrFail();

        $id     = intval($request->input('entity_id'));
        $entity = null;

        switch ($context->id) {
            case ForumContext::CONTEXT_TRANSLATION:
                $entity = Translation::active()
                    ->where('id', $id)
                    ->firstOrFail();
                break;
            
            case ForumContext::CONTEXT_SENTENCE:
                $entity = Sentence::findOrFail($id);
                break;

            default:
                return null;
        }

        return [
            'id'     => $context->id,
            'entity' => $entity
        ];
    }
}