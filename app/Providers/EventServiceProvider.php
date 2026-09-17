<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use App\Listeners\LogUser;
use App\Listeners\ResetPassword;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

use App\Models\User;
use App\Models\Meeting;
use App\Models\GlobalConfig;
use App\Models\Plan;
use App\Models\Coupon;
use App\Models\TaxRate;
use App\Models\Contact;
use App\Models\Page;
use App\Observers\UserObserver;
use App\Observers\MeetingObserver;
use App\Observers\GlobalConfigObserver;
use App\Observers\PlanObserver;
use App\Observers\CouponObserver;
use App\Observers\TaxrateObserver;
use App\Observers\ContactObserver;
use App\Observers\PageObserver;


class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Login::class => [
            LogUser::class,
        ],
        PasswordReset::class => [
            ResetPassword::class,
        ],
        
        
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(UserObserver::class);
        Meeting::observe(MeetingObserver::class);
        GlobalConfig::observe(GlobalConfigObserver::class);
        Plan::observe(PlanObserver::class);
        Coupon::observe(CouponObserver::class);
        TaxRate::observe(TaxrateObserver::class);
        Contact::observe(ContactObserver::class);
        Page::observe(PageObserver::class);
    }
}
