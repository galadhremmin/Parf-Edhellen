<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class PhrasesController extends Controller
{
    public function index() 
    {
        return view('phrases.index');
    }
}
