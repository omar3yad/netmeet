<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Plan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class UserObserver extends BaseObserver
{

    public $afterCommit = true;

    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        if(Auth::user()){
            $this->logActivity($user->id, 'User', config('constants.LOG_EVENTS.user_created'), 'Username: '.$user->username);
        }else{
            $this->logActivity($user->id, 'User', config('constants.LOG_EVENTS.user_registered'), 'Username: '.$user->username);
        }
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        if(request()->has('checked')){
            if(request()->has('checked') && request()->get('checked') == "true"){
                $status = 'Username: '.$user->username.'- from inactive to active';
            }else{
                $status = 'Username: '.$user->username.'- from active to inactive';
            }
            $this->logActivity($user->id, 'User', config('constants.LOG_EVENTS.user_status_updated'), $status);
        }else{
            $columns = Schema::getColumnListing('users');
        $updatedFields = '';
        $i = 0;
        foreach ($columns as $col) {
            if ($user->isDirty($col) && $col != 'updated_at') { 
                $newValue = $user->{$col};
                $oldValue = $user->getOriginal($col);
                $fieldStr = ucwords(str_replace("_", " ", $col));
                if($col == 'plan_id'){
                    $newPlanname = Plan::select('name')->where('id',$newValue)->first()->name;
                    $oldPlanname = Plan::select('name')->where('id',$oldValue)->first()->name;
                    $updatedFields.= 'from '. $oldPlanname .' To '. $newPlanname;
                }else{
                    if(is_array($newValue)){
                        $newValue = json_encode($newValue);
                    }
                    if($i == 0){
                        $updatedFields.= $fieldStr .' from '. $oldValue .' To '. $newValue;
                    }else{
                        $updatedFields.= ', ' .$fieldStr .' from '. $oldValue .' To '. $newValue;
                    }
                    $i++;
                }
                
            }
        }
        
        if(request()->has('plan_id')){
            $this->logActivity($user->id, 'User', config('constants.LOG_EVENTS.user_plan_updated'), 'Username: '.$user->username.' - '.'Plan change '.$updatedFields);
        }else{
            $this->logActivity($user->id, 'User', config('constants.LOG_EVENTS.user_details_updated'), 'Username: '.$user->username.' - '.$updatedFields);
        }
        }
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        $this->logActivity($user->id, 'User', config('constants.LOG_EVENTS.user_deleted'), 'Username: '.$user->username);
    }

    /**
     * Handle the User "restored" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
