<?php

namespace App\Http\Controllers;

use App\Models\{
    ForumThread,
    ForumPost
};
use App\Repositories\Interfaces\IAuditTrailRepository;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    protected $_auditTrail;

    public function __construct(IAuditTrailRepository $auditTrail) 
    {
        $this->_auditTrail = $auditTrail;
    }

    public function index(Request $request)
    {
        return view('forum.index');
    }
}
