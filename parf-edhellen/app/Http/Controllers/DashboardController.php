<?php

namespace App\Http\Controllers;

use App\Models\{User, UserGroup};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $g = UserGroup::forUser($user)->get();
        return $g;

        return view('dashboard.index');
    }
}
