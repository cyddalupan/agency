<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportsIndexController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }
}
