<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class CabinetController extends Controller
{
    public function index(): View
    {
        return view('cabinet.dashboard');
    }
}
