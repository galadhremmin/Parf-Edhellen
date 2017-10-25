<?php

namespace App\Http\Controllers\Resources;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Discuss\ContextFactory;
use App\Adapters\DiscussAdapter;
use App\Models\{
    ForumThread,
    ForumPost
};

class DiscussController extends Controller
{
    protected $_discussAdapter;
    protected $_contextFactory;

    public function __construct(DiscussAdapter $discussAdapter, ContextFactory $contextFactory) 
    {
        $this->_discussAdapter = $discussAdapter;
        $this->_contextFactory = $contextFactory;
    }

    public function index(Request $request)
    {
        $threads = ForumThread::where('number_of_posts', '>', 0)
            ->with('account')
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $adapted = $this->_discussAdapter->adaptThreads($threads);
        return view('discuss.index', [
            'threads' => $adapted
        ]);
    }

    public function show(Request $request, int $id)
    {
        $thread = ForumThread::findOrFail($id);

        $context = $this->_contextFactory->create($thread->entity_type);
        if (! $context->available($thread, $request->user())) {
            abort(403);
        }

        return view('discuss.show', [
            'thread'  => $thread,
            'context' => $context
        ]);
    }
}
