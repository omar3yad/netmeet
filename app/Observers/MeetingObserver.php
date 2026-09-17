<?php

namespace App\Observers;

use App\Models\Meeting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class MeetingObserver extends BaseObserver
{
    /**
     * Handle the Meeting "created" event.
     *
     * @param  \App\Models\Meeting  $meeting
     * @return void
     */
    public function created(Meeting $meeting)
    {
        $requestData = request()->all();
        $this->logActivity($meeting->id, 'Meeting', config('constants.LOG_EVENTS.meeting_created'), 'Meeting ID: '.$meeting->meeting_id);
    }

    /**
     * Handle the Meeting "updated" event.
     *
     * @param  \App\Models\Meeting  $meeting
     * @return void
     */
    public function updated(Meeting $meeting)
    {
        if(request()->has('checked')){
            if(request()->has('checked') && request()->get('checked') == "true"){
                $status = 'Meeting ID: '.$meeting->meeting_id.' - from inactive to active';
            }else{
                $status = 'Meeting ID: '.$meeting->meeting_id.' - from active to inactive';
            }
            $this->logActivity($meeting->id, 'Meeting', config('constants.LOG_EVENTS.meeting_status_updated'), $status);
        }else{
            $columns = Schema::getColumnListing('meetings');
            $updatedFields = '';
            $i = 0;
            foreach ($columns as $col) {
                if ($meeting->isDirty($col) && $col != 'updated_at') { 
                    $newValue = $meeting->{$col};
                    $oldValue = $meeting->getOriginal($col);
                    $fieldStr = ucwords(str_replace("_", " ", $col));
                    if($i == 0){
                        $updatedFields.= $fieldStr .' from '. $oldValue .' To '. $newValue;
                    }else{
                        $updatedFields.= ', ' .$fieldStr .' from '. $oldValue .' To '. $newValue;
                    }
                    $i++;
                }
            }
            $this->logActivity($meeting->id, 'Meeting', config('constants.LOG_EVENTS.meeting_details_updated'), 'Meeting ID: '.$meeting->meeting_id.' - '.$updatedFields);
        }
    }

    /**
     * Handle the Meeting "deleted" event.
     *
     * @param  \App\Models\Meeting  $meeting
     * @return void
     */
    public function deleted(Meeting $meeting)
    {
        $this->logActivity($meeting->id, 'Meeting', config('constants.LOG_EVENTS.meeting_deleted'), 'Meeting ID: '.$meeting->meeting_id);
    }

    /**
     * Handle the Meeting "restored" event.
     *
     * @param  \App\Models\Meeting  $meeting
     * @return void
     */
    public function restored(Meeting $meeting)
    {
        //
    }

    /**
     * Handle the Meeting "force deleted" event.
     *
     * @param  \App\Models\Meeting  $meeting
     * @return void
     */
    public function forceDeleted(Meeting $meeting)
    {
        //
    }
}
