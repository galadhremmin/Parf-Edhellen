<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Helpers\MarkdownParser;
use App\Http\Controllers\Controller;

class UtilityApiController extends Controller
{
    public function parseMarkdown(Request $request)
    {
        $this->validate($request, [
            'markdown' => 'required'
        ]);

        $markdown = $request->input('markdown');
        $parser = new MarkdownParser();

        return [ 'html' => $parser->parse($markdown) ];
    }
}