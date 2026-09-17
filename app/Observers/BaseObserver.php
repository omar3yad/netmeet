<?php

namespace App\Observers;
use App\Models\LogActivity;
use Illuminate\Support\Facades\Auth;

class BaseObserver {

    // Log All Activities
    public function logActivity($primary, $model, $eventtype, $log)
    {
        $clientIP = \Request::ip();
        LogActivity::insert([
            'primary_id'       => $primary,
            'user_id'       => Auth::user() ? Auth::user()->id : $primary,
            'model'      => $model,
            'event_type'    => $eventtype,
            'log'    => $log,
            'ip'            => $clientIP,
        ]);   
    }

}
