<?php

namespace App\Http\Controllers\Api\v2;

use Illuminate\Http\Request;

use App\Http\Controllers\Abstracts\Controller;
use App\Interfaces\IMarkdownParser;
use App\Models\FailedJob;
use App\Models\SystemError;
use App\Repositories\SystemErrorRepository;

class UtilityApiController extends Controller
{
    const DEFAULT_SYSTEM_ERROR_CATEGORY = 'frontend';

    private $_markdownParser;
    /**
     * @var SystemErrorRepository
     */
    private $_systemErrorRepository;

    public function __construct(IMarkdownParser $markdownParser, SystemErrorRepository $systemErrorRepository)
    {
        $this->_markdownParser = $markdownParser;
        $this->_systemErrorRepository = $systemErrorRepository;
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
            'error'    => 'string|nullable',
            'category' => 'string|nullable'
        ]);

        $category = $request->has('category')
            ? 'frontend-'.substr($request->input('category'), 0, 16)
            : self::DEFAULT_SYSTEM_ERROR_CATEGORY;

        $this->_systemErrorRepository->saveFrontendException(
            $request->input('url'),
            $request->input('message'),
            $request->input('error'),
            $category
        );

        return response(null, 201);
    }

    public function getErrors(Request $request)
    {
        $from = intval($request->query('from', 0));
        $to   = intval($request->query('to', 100));

        $query = SystemError::orderBy('id', 'desc')
            ->whereNotIn('category', ['http-401', 'http-404']);

        $length = $query->count();
        $errors = $query
            ->skip($from)
            ->take($to)
            ->get();

        return [
            'errors' => $errors,
            'length' => $length
        ];
    }

    public function getFailedJobs(Request $request)
    {
        $from = intval($request->query('from', 0));
        $to   = intval($request->query('to', 100));

        $query = FailedJob::orderBy('id', 'desc');

        $length = $query->count();
        $errors = $query
            ->skip($from)
            ->take($to)
            ->get();

        return [
            'errors' => $errors,
            'length' => $length
        ];
    }
}
