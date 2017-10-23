<?php

namespace App\Http\Controllers\Resources;

use Illuminate\Http\Request;

use App\Models\{
    ForumThread,
    ForumPost
};
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\IAuditTrailRepository;

class DiscussController extends Controller
{
    protected $_auditTrail;

    public function __construct(IAuditTrailRepository $auditTrail) 
    {
        $this->_auditTrail = $auditTrail;
    }

    public function index(Request $request)
    {
        $threads = ForumThread::where('number_of_posts', '>', 0)
            ->with('account')
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('discuss.index', [
            'threads' => $threads
        ]);
    }
}
