<?php

namespace App\Http\Controllers\Api\v3;

use App\Helpers\AgGridFilterHelper;
use App\Http\Controllers\Abstracts\Controller;
use App\Interfaces\IMarkdownParser;
use App\Models\FailedJob;
use App\Models\SystemError;
use App\Repositories\AccountIpHistoryRepository;
use App\Repositories\SystemErrorRepository;
use Illuminate\Http\Request;

class UtilityApiController extends Controller
{
    const DEFAULT_SYSTEM_ERROR_CATEGORY = 'frontend';

    const DEFAULT_PAGE_SIZE = 100;

    const MAX_PAGE_SIZE = 1000;

    /**
     * The columns the exception log grid is allowed to filter on, as a map of the grid's field
     * names to the SQL expressions they correspond to.
     */
    const SYSTEM_ERROR_FILTER_EXPRESSIONS = [
        'createdAt' => 'created_at',
        'message' => 'message',
        'error' => 'error',
        'url' => 'url',
        'accountId' => 'account_id',
        'duration' => 'duration',
        'sessionId' => 'session_id',
        'ip' => 'CAST(ip AS CHAR)',
        'file' => 'file',
        'line' => 'line',
        'userAgent' => 'user_agent',
        'category' => 'category',
    ];

    private IMarkdownParser $_markdownParser;

    private SystemErrorRepository $_systemErrorRepository;

    private AccountIpHistoryRepository $_accountIpHistoryRepository;

    private AgGridFilterHelper $_agGridFilterHelper;

    public function __construct(IMarkdownParser $markdownParser, SystemErrorRepository $systemErrorRepository,
        AccountIpHistoryRepository $accountIpHistoryRepository, AgGridFilterHelper $agGridFilterHelper)
    {
        $this->_markdownParser = $markdownParser;
        $this->_systemErrorRepository = $systemErrorRepository;
        $this->_accountIpHistoryRepository = $accountIpHistoryRepository;
        $this->_agGridFilterHelper = $agGridFilterHelper;
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

    public function reportMissingWord(Request $request)
    {
        $this->validate($request, [
            'word' => 'required|string|max:200',
        ]);

        SystemError::firstOrCreate([
            'category' => 'missing-word',
            'message' => $request->input('word'),
        ], [
            'url' => $request->header('Referer', ''),
            'error' => '',
        ]);

        return response(null, 204);
    }

    public function logError(Request $request)
    {
        $this->validate($request, [
            'message' => 'string|required|max:2000',
            'url' => 'string|required|max:500',
            'error' => 'string|nullable|max:5000',
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
        $this->validate($request, [
            'offset' => 'sometimes|integer|min:0',
            'limit' => 'sometimes|integer|min:1|max:'.self::MAX_PAGE_SIZE,
            'account_id' => 'sometimes|integer|min:1',
            'ip' => 'sometimes|string|max:45',
            'filters' => 'sometimes|string|max:4096',
        ]);

        $offset = intval($request->query('offset', 0));
        $limit = intval($request->query('limit', self::DEFAULT_PAGE_SIZE));
        $category = $request->query('category');
        $accountId = $request->query('account_id');
        $ip = $request->query('ip');
        $filterModel = $this->_agGridFilterHelper->parse($request->query('filters'));

        $query = SystemError::orderBy('id', 'desc')
            ->whereNotIn('category', ['http-401', 'http-404']);

        if ($category !== null) {
            $query->where('category', $category);
        }

        if ($ip !== null) {
            // A specific IP address takes precedence over the account's entire IP history.
            $query->where('ip', $ip);
        } elseif ($accountId !== null) {
            $accountId = intval($accountId);
            $ipAddresses = $this->_accountIpHistoryRepository->getIpAddresses($accountId);

            $query->where(function ($query) use ($accountId, $ipAddresses) {
                $query->where('account_id', $accountId);

                if (! empty($ipAddresses)) {
                    $query->orWhereIn('ip', $ipAddresses);
                }
            });
        }

        $this->_agGridFilterHelper->apply($query, $filterModel, self::SYSTEM_ERROR_FILTER_EXPRESSIONS);

        $length = $query->count();
        $errors = $query
            ->skip($offset)
            ->take($limit)
            ->get();

        return [
            'errors' => $errors,
            'length' => $length,
        ];
    }

    public function getAccountIpHistory(Request $request, int $id)
    {
        return [
            'accountId' => $id,
            'ipAddresses' => $this->_accountIpHistoryRepository->getIpHistory($id),
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
        $this->validate($request, [
            'offset' => 'sometimes|integer|min:0',
            'limit' => 'sometimes|integer|min:1|max:'.self::MAX_PAGE_SIZE,
        ]);

        $offset = intval($request->query('offset', 0));
        $limit = intval($request->query('limit', self::DEFAULT_PAGE_SIZE));

        $query = FailedJob::orderBy('id', 'desc');

        $length = $query->count();
        $errors = $query
            ->skip($offset)
            ->take($limit)
            ->get();

        return [
            'errors' => $errors,
            'length' => $length,
        ];
    }
}
