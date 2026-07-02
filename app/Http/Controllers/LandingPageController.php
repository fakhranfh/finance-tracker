<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function show(): View
    {
        return view('landing-page');
    }
}
