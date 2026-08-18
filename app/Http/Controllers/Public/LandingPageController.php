<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function __invoke(): View
    {
        // FR-PUB-001: no applicant statistics are shown on the landing page.
        return view('public.landing');
    }
}
