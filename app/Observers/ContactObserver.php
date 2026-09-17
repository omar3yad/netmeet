<?php

namespace App\Observers;

use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ContactObserver extends BaseObserver
{
    /**
     * Handle the Contact "created" event.
     *
     * @param  \App\Models\Contact  $contact
     * @return void
     */
    public function created(Contact $contact)
    {
        $requestData = request()->all();
        if(isset($requestData['name'])){
            $this->logActivity($contact->id, 'Contact', config('constants.LOG_EVENTS.contact_created'), 'Contact Added : '.$requestData['name']);
        }
        
    }

    /**
     * Handle the Contact "updated" event.
     *
     * @param  \App\Models\Contact  $contact
     * @return void
     */
    public function updated(Contact $contact)
    {
        $columns = Schema::getColumnListing('contacts');

            $updatedFields = '';
            $i = 0;
            foreach ($columns as $col) {
                if ($contact->isDirty($col) && $col != 'updated_at') { 
                    $newValue = $contact->{$col};
                    $oldValue = $contact->getOriginal($col);
                    $fieldStr = ucwords(str_replace("_", " ", $col));
                    
                    if($i == 0){
                        $updatedFields.= $fieldStr .' from '. $oldValue .' To '. $newValue;
                    }else{
                        $updatedFields.= ', ' .$fieldStr .' from '. $oldValue .' To '. $newValue;                        
                    }
                    $i++;
                }
            }
            $this->logActivity($contact->id, 'Contact', config('constants.LOG_EVENTS.contact_details_updated'), 'Contact Added : '.$contact->name.' - '.$updatedFields);
    }

    /**
     * Handle the Contact "deleted" event.
     *
     * @param  \App\Models\Contact  $contact
     * @return void
     */
    public function deleted(Contact $contact)
    {
        $this->logActivity($contact->id, 'Contact', config('constants.LOG_EVENTS.contact_deleted'), 'Deleted Contact - '.$contact->name);
    }

    /**
     * Handle the Contact "restored" event.
     *
     * @param  \App\Models\Contact  $contact
     * @return void
     */
    public function restored(Contact $contact)
    {
        //
    }

    /**
     * Handle the Contact "force deleted" event.
     *
     * @param  \App\Models\Contact  $contact
     * @return void
     */
    public function forceDeleted(Contact $contact)
    {
        //
    }
}
