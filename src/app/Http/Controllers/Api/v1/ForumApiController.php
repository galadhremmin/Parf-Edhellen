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
     * HTTP POST. Creates a new forum post for a translation.
     *
     * @param Request $request
     * @return response 201 on success
     */
    public function postForTranslation(Request $request)
    {
        $this->validate($request, [
            'content'        => 'required|string',
            'translation_id' => 'required|exists:translations,id'
        ]);

        $topic = 't_' . $request->input('translation_id');
        $post  = $this->createPost($request, $topic);

        return response($post, 201);
    }

    /**
     * HTTP POST. Creates a new forum post for a sentence.
     *
     * @param Request $request
     * @return response 201 on success
     */
    public function postForSentence(Request $request)
    {
        $this->validate($request, [
            'content'     => 'required|string',
            'sentence_id' => 'required|exists:sentences,id'
        ]);

        $topic = 's_' . $request->input('sentence_id');
        $post  = $this->createPost($request, $topic);
        
        return response($post, 201);
    }

    private function createPost(Request $request, string $topic)
    {
        $post = ForumPost::create([
            'topic'      => $topic,
            'content'    => $request->input('content'),
            'account_id' => $request->user()->id
        ]);

        return $post;
    }
}