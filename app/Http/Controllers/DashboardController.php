<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function administrator(): View
    {
        return view('dashboards.administrator');
    }

    public function recruiter(): View
    {
        return view('dashboards.recruiter');
    }

    public function talent(): View
    {
        return view('dashboards.talent');
    }
}
