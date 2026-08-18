<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class StaticPageController extends Controller
{
    public function privacy(): View
    {
        return view('public.privacy');
    }

    public function terms(): View
    {
        return view('public.terms');
    }

    public function accessibility(): View
    {
        return view('public.accessibility');
    }

    public function support(): View
    {
        return view('public.support');
    }

    public function systemStatus(): View
    {
        return view('public.system-status');
    }
}
