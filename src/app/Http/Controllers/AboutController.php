<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Abstracts\Controller;
use App\Models\Language;

class AboutController extends Controller
{
    public function index()
    {
        $languages = Language::whereNotNull('short_name')->orderBy('name')->get();

        return view('about.index', [
            'languages' => $languages,
        ]);
    }

    public function cookies()
    {
        return view('about.cookies');
    }

    public function privacy()
    {
        return view('about.privacy');
    }
}
