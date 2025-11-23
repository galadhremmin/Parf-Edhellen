<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Controllers\Abstracts\Controller;
use App\Interfaces\IMarkdownParser;
use App\Models\FailedJob;
use App\Models\SystemError;
use App\Repositories\SystemErrorRepository;
use Illuminate\Http\Request;

class UtilityApiController extends Controller
{
    const DEFAULT_SYSTEM_ERROR_CATEGORY = 'frontend';

    private IMarkdownParser $_markdownParser;

    private SystemErrorRepository $_systemErrorRepository;

    public function __construct(IMarkdownParser $markdownParser, SystemErrorRepository $systemErrorRepository)
    {
        $this->_markdownParser = $markdownParser;
        $this->_systemErrorRepository = $systemErrorRepository;
    }

    public function parseMarkdown(Request $request)
    {
        $this->validate($request, [
            'markdown' => 'sometimes|required|string',
            'markdowns' => 'sometimes|required|array',
        ]);

        $markdown = $request->input('markdown');
        if ($markdown) {
            return ['html' => $this->_markdownParser->parseMarkdown($markdown)];
        }

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
            'message' => 'string|required',
            'url' => 'string|required',
            'error' => 'string|nullable',
            'category' => ['nullable', 'string', 'regex:/^[a-z0-9\-]+$/'],
            'duration' => 'numeric|nullable',
        ]);

        $category = $request->has('category')
            ? 'frontend-'.substr($request->input('category'), 0, 16)
            : self::DEFAULT_SYSTEM_ERROR_CATEGORY;

        $this->_systemErrorRepository->saveFrontendException(
            $request->input('url'),
            $request->input('message'),
            $request->input('error') ?? '',
            $category,
            $request->input('duration')
        );

        return response(null, 201);
    }

    public function getErrors(Request $request)
    {
        $from = intval($request->query('from', 0));
        $to = intval($request->query('to', 100));
        $category = $request->query('category');

        $query = SystemError::orderBy('id', 'desc')
            ->whereNotIn('category', ['http-401', 'http-404']);

        if ($category !== null) {
            $query->where('category', $category);
        }

        $length = $query->count();
        $errors = $query
            ->skip($from)
            ->take($to)
            ->get();

        return [
            'errors' => $errors,
            'length' => $length,
        ];
    }

    public function deleteError(Request $request, int $id)
    {
        $user = $request->user();
        if ($user === null || ! $user->isRoot()) {
            abort(403, 'Access denied');
        }

        $error = SystemError::find($id);
        if ($error === null) {
            abort(404, 'Error not found');
        }

        $error->delete();

        return response(null, 204);
    }

    public function deleteErrorsByCategory(Request $request)
    {
        $user = $request->user();
        if ($user === null || ! $user->isRoot()) {
            abort(403, 'Access denied');
        }

        $this->validate($request, [
            'category' => 'required|string',
            'year' => 'sometimes|integer',
            'week' => 'sometimes|integer',
        ]);

        $category = $request->query('category');
        $year = $request->query('year');
        $week = $request->query('week');

        $query = SystemError::where('category', $category);

        if ($year !== null && $week !== null) {
            $query->whereRaw('YEAR(created_at) = ?', [$year])
                  ->whereRaw('WEEK(created_at) = ?', [$week]);
        }

        $deleted = $query->delete();

        return [
            'deleted' => $deleted,
        ];
    }

    public function getFailedJobs(Request $request)
    {
        $from = intval($request->query('from', 0));
        $to = intval($request->query('to', 100));

        $query = FailedJob::orderBy('id', 'desc');

        $length = $query->count();
        $errors = $query
            ->skip($from)
            ->take($to)
            ->get();

        return [
            'errors' => $errors,
            'length' => $length,
        ];
    }
}
