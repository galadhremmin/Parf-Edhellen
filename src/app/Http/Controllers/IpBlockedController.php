<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Abstracts\Controller;

class IpBlockedController extends Controller
{
    public function index()
    {
        return view('errors.blocked');
    }
}
