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
            'markdown'  => 'sometimes|required|string',
            'markdowns' => 'sometimes|required|array'
        ]);

        $parser = new MarkdownParser();

        $markdown = $request->input('markdown');
        if ($markdown)
            return [ 'html' => $parser->parse($markdown) ];
        
        $markdowns = $request->input('markdowns');
        $keys = array_keys($markdowns);
        $html = [];

        foreach ($keys as $key) {
            $html[$key] = $parser->parse($markdowns[$key]);
        }

        return $html;
    }
}