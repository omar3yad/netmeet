<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Plan;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the home page.
     */
    public function index(Request $request)
    {
        $page = Page::where('slug', 'home')->select('content')->first();
        $plans = Plan::where('status', 1)->get();

        return view('home', ['plans' => $plans, 'page' => $page]);
    }
}
