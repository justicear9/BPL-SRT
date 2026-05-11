<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AlphaReportController extends Controller
{
    public function index(): View
    {
        return view('content.reports.alpha-report');
    }
}
