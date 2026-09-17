<?php

namespace App\Observers;

use App\Models\Plan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PlanObserver extends BaseObserver
{
    /**
     * Handle the Plan "created" event.
     *
     * @param  \App\Models\Plan  $plan
     * @return void
     */
    public function created(Plan $plan)
    {
        $requestData = request()->all();
        $this->logActivity($plan->id, 'Plan', config('constants.LOG_EVENTS.plan_created'), 'Plan Name: '.$requestData['name']);
    }

    /**
     * Handle the Plan "updated" event.
     *
     * @param  \App\Models\Plan  $plan
     * @return void
     */
    public function updated(Plan $plan)
    {
        if(request()->has('checked')){
            if(request()->has('checked') && request()->get('checked') == "true"){
                $status = 'Plan Name: '.$plan->name.' - from inactive to active';
            }else{
                $status = 'Plan Name: '.$plan->name.' - from active to inactive';
            }
            $this->logActivity($plan->id, 'Plan', config('constants.LOG_EVENTS.plan_status_updated'), $status);
        }else{
            $columns = Schema::getColumnListing('plans');

            $updatedFields = '';
            $i = 0;
            foreach ($columns as $col) {
                if ($plan->isDirty($col) && $col != 'updated_at') { 
                    $newValue = $plan->{$col};
                    $oldValue = $plan->getOriginal($col);
                    if($col == 'features'){
                        $fields = [];
                        foreach($newValue as $key => $val){
                            $oldValue = (array)$oldValue;
                            if(isset($oldValue[$key]) && $oldValue[$key] != $val){

                                $fields[$key] = $val == 1 ? 'On' : 'Off';
                            }
                        }
                        $fieldStr = http_build_query($fields,'',', ');
                    }else{
                        $fieldStr = ucwords(str_replace("_", " ", $col));
                    }
                    
                    if($i == 0){
                        if($col == 'features'){
                            $updatedFields.= ' features: '. $fieldStr;
                        }else{
                            $updatedFields.= $fieldStr .' from '. $oldValue .' To '. $newValue;
                        }
                    }else{
                        if($col == 'features'){
                            $updatedFields.= ', features: '. $fieldStr;
                        }else{
                            $updatedFields.= ', ' .$fieldStr .' from '. $oldValue .' To '. $newValue;
                        }
                        
                    }
                    $i++;
                }
            }
            $this->logActivity($plan->id, 'Plan', config('constants.LOG_EVENTS.plan_details_updated'), 'Plan Name: '.$plan->name.' - '.$updatedFields);
        }
    }

    /**
     * Handle the Plan "deleted" event.
     *
     * @param  \App\Models\Plan  $plan
     * @return void
     */
    public function deleted(Plan $plan)
    {
        $this->logActivity($plan->id, 'Plan', config('constants.LOG_EVENTS.plan_deleted'), 'Plan Name: '.$plan->name);
    }

    /**
     * Handle the Plan "restored" event.
     *
     * @param  \App\Models\Plan  $plan
     * @return void
     */
    public function restored(Plan $plan)
    {
        //
    }

    /**
     * Handle the Plan "force deleted" event.
     *
     * @param  \App\Models\Plan  $plan
     * @return void
     */
    public function forceDeleted(Plan $plan)
    {
        //
    }
}
