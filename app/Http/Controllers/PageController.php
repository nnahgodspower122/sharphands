<?php

namespace App\Http\Controllers;

use App\Models\Service;

class PageController extends Controller
{
    public function home()
    {
        return view('pages.home', [
            'services' => Service::query()->orderBy('name')->get(),
        ]);
    }

    public function about()
    {
        return view('pages.about');
    }

    public function contact()
    {
        return view('pages.contact');
    }
}
