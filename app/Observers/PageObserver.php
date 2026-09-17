<?php

namespace App\Observers;

use App\Models\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PageObserver extends BaseObserver
{
    /**
     * Handle the Page "created" event.
     *
     * @param  \App\Models\Page  $page
     * @return void
     */
    public function created(Page $page)
    {
        $this->logActivity($page->id, 'Page', config('constants.LOG_EVENTS.page_created'), 'Page Title: '.$page->title);
    }

    /**
     * Handle the Page "updated" event.
     *
     * @param  \App\Models\Page  $page
     * @return void
     */
    public function updated(Page $page)
    {
        $columns = Schema::getColumnListing('pages');
            $updatedFields = '';
            $i = 0;
            foreach ($columns as $col) {
                if ($page->isDirty($col) && $col != 'updated_at') {
                    $newValue = $page->{$col};
                    $oldValue = $page->getOriginal($col);
                    $fieldStr = ucwords(str_replace("_", " ", $col));
                    if($i == 0){
                        if($col == 'content') {
                            $updatedFields.= 'Page Content Updated';
                        }else{
                            $updatedFields.= $fieldStr .' from '. $oldValue .' To '. $newValue;
                        }
                        
                    }else{
                        if($col == 'content') {
                            $updatedFields.= ', Page Content Updated';
                        }else{
                            $updatedFields.= ', ' .$fieldStr .' from '. $oldValue .' To '. $newValue;
                        }
                    }
                    $i++;
                }
            }
            $this->logActivity($page->id, 'Page', config('constants.LOG_EVENTS.page_details_updated'), 'Page Title: '.$page->title.' - '.$updatedFields);
    }

    /**
     * Handle the Page "deleted" event.
     *
     * @param  \App\Models\Page  $page
     * @return void
     */
    public function deleted(Page $page)
    {
        $this->logActivity($page->id, 'Page', config('constants.LOG_EVENTS.page_deleted'), 'Page Title: '.$page->title);
    }

    /**
     * Handle the Page "restored" event.
     *
     * @param  \App\Models\Page  $page
     * @return void
     */
    public function restored(Page $page)
    {
        //
    }

    /**
     * Handle the Page "force deleted" event.
     *
     * @param  \App\Models\Page  $page
     * @return void
     */
    public function forceDeleted(Page $page)
    {
        //
    }
}
