<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Models\Meeting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
   public function boot()
{
    // Only attach meetings to the main layout for authenticated users
     View::composer('layouts.app', function ($view) {
        if (auth()->check()) {
            $allMeetings = Meeting::where('user_id', auth()->id())->orderBy('id', 'DESC')->get();
            $navbarMeetings = $allMeetings->take(5);
            $totalMeetings = $allMeetings->count();
            
            $view->with([
                'meetings' => $navbarMeetings,
                'allMeetings' => $allMeetings,
                'totalMeetings' => $totalMeetings,
                'hasMoreMeetings' => $totalMeetings > 5
            ]);
        }
    });

    Schema::defaultStringLength(191);
    Paginator::useBootstrap();
}
}
