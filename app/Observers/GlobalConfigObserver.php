<?php

namespace App\Observers;

use App\Models\GlobalConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class GlobalConfigObserver extends BaseObserver
{
    /**
     * Handle the GlobalConfig "created" event.
     *
     * @param  \App\Models\GlobalConfig  $globalConfig
     * @return void
     */
    public function created(GlobalConfig $globalConfig)
    {
    }

    /**
     * Handle the GlobalConfig "updated" event.
     *
     * @param  \App\Models\GlobalConfig  $globalConfig
     * @return void
     */
    public function updated(GlobalConfig $globalConfig)
    {
        if(request()->has('checked')){
            if(request()->has('checked') && request()->get('checked') == "true"){
                $status = 'from inactive to active';
            }else{
                $status = 'from active to inactive';
            }
            $this->logActivity($globalConfig->id, 'GlobalConfig', config('constants.LOG_EVENTS.globalconfig_status_updated'), $status);
        }else{
            $columns = Schema::getColumnListing('global_config');

            $updatedFields = '';
            $i = 0;
            foreach ($columns as $key => $col) {
                if ($globalConfig->isDirty($col) && $col != 'updated_at') { 
                    $newValue = $globalConfig->{$col};
                    $oldValue = $globalConfig->getOriginal($col);
                    $oldValue = empty($oldValue) || is_null($oldValue) ? 'empty' : $oldValue;
                    
                    $fieldStr = ucwords(str_replace("_", " ", $globalConfig->key));
                    
                    if($i == 0){
                            $updatedFields.= $fieldStr .' from '. $oldValue .' To '. $newValue;
                    }else{
                        $updatedFields.= ', ' .$fieldStr .' from '. $oldValue .' To '. $newValue;                        
                    }
                    $i++;
                }
            }
            $this->logActivity($globalConfig->id, 'GlobalConfig', config('constants.LOG_EVENTS.globalconfig_details_updated'), $updatedFields);
        }
    }

    /**
     * Handle the GlobalConfig "deleted" event.
     *
     * @param  \App\Models\GlobalConfig  $globalConfig
     * @return void
     */
    public function deleted(GlobalConfig $globalConfig)
    {
        //
    }

    /**
     * Handle the GlobalConfig "restored" event.
     *
     * @param  \App\Models\GlobalConfig  $globalConfig
     * @return void
     */
    public function restored(GlobalConfig $globalConfig)
    {
        //
    }

    /**
     * Handle the GlobalConfig "force deleted" event.
     *
     * @param  \App\Models\GlobalConfig  $globalConfig
     * @return void
     */
    public function forceDeleted(GlobalConfig $globalConfig)
    {
        //
    }
}
