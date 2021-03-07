<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Abstracts\Controller;

class AboutController extends Controller
{
    public function index() 
    {
        return view('about.index');
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
