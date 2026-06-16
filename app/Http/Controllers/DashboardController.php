<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $agency = tenant_agency();
        $user = auth()->user();

        return view('dashboard', compact('agency', 'user'));
    }
}
