<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdFaqController extends Controller
{
    public function index()
    {
        return view('AdHelp.AdFaq');
    }
}
