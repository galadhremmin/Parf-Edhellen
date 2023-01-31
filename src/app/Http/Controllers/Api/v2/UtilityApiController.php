<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;

use App\Http\Controllers\Abstracts\Controller;
use App\Interfaces\IMarkdownParser;
use App\Models\SystemError;

class UtilityApiController extends Controller
{
    const DEFAULT_SYSTEM_ERROR_CATEGORY = 'frontend';
    const RESTRICTED_SYSTEM_ERROR_CATEGORIES = ['backend'];

    private $_markdownParser;

    public function __construct(IMarkdownParser $markdownParser)
    {
        $this->_markdownParser = $markdownParser;
    }

    public function parseMarkdown(Request $request)
    {
        $this->validate($request, [
            'markdown'  => 'sometimes|required|string',
            'markdowns' => 'sometimes|required|array'
        ]);

        $markdown = $request->input('markdown');
        if ($markdown)
            return [ 'html' => $this->_markdownParser->parseMarkdown($markdown) ];
        
        $markdowns = $request->input('markdowns');
        $keys = array_keys($markdowns);
        $html = [];

        foreach ($keys as $key) {
            $html[$key] = $this->_markdownParser->parseMarkdown($markdowns[$key]);
        }

        return $html;
    }

    public function logError(Request $request) 
    {
        $this->validate($request, [
            'message'  => 'string|required',
            'url'      => 'string|required',
            'error'    => 'string',
            'category' => 'string'
        ]);

        $category = $request->has('category') ? $request->input('category') : null;
        if ($category === null || in_array($category, self::RESTRICTED_SYSTEM_ERROR_CATEGORIES)) {
            $category = self::DEFAULT_SYSTEM_ERROR_CATEGORY;
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (strlen($userAgent) > 190) {
            $userAgent = substr($userAgent, 0, 190).'...';
        }

        $user = $request->user();
        SystemError::create([
            'message'    => $request->input('message'),
            'url'        => $request->input('url'),
            'ip'         => isset($_SERVER['REMOTE_ADDR'])
                ? $_SERVER['REMOTE_ADDR']
                : null,
            'error'      => $request->has('error') 
                ? $request->input('error') 
                : null,
            'account_id' => $user !== null
                ? $user->id 
                : null,
            'is_common'  => 0,
            'category'   => $category,
            'user_agent' => $userAgent
        ]);

        return response(null, 201);
    }

    public function getErrors()
    {
        $errors = SystemError::orderBy('id', 'desc')
            ->paginate(10);

        return $errors;
    }
}
