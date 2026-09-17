<?php

namespace App\Observers;

use App\Models\TaxRate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class TaxrateObserver extends BaseObserver
{
    /**
     * Handle the TaxRate "created" event.
     *
     * @param  \App\Models\TaxRate  $taxRate
     * @return void
     */
    public function created(TaxRate $taxRate)
    {
        $requestData = request()->all();
        $this->logActivity($taxRate->id, 'TaxRate', config('constants.LOG_EVENTS.taxrate_created'), 'Tax Rate Name: '.$requestData['name']);
    }

    /**
     * Handle the TaxRate "updated" event.
     *
     * @param  \App\Models\TaxRate  $taxRate
     * @return void
     */
    public function updated(TaxRate $taxRate)
    {
        if(request()->has('checked')){
            if(request()->has('checked') && request()->get('checked') == "true"){
                $status = 'TaxRate: '.$taxRate->name.' - from inactive to active';
            }else{
                $status = 'TaxRate: '.$taxRate->name.' - from active to inactive';
            }
            $this->logActivity($taxRate->id, 'TaxRate', config('constants.LOG_EVENTS.taxrate_status_updated'), $status);
        }else{
            $columns = Schema::getColumnListing('tax_rates');

            $updatedFields = '';
            $i = 0;
            foreach ($columns as $col) {
                if ($taxRate->isDirty($col) && $col != 'updated_at') { 
                    $newValue = $taxRate->{$col};
                    $oldValue = $taxRate->getOriginal($col);
                    $fieldStr = ucwords(str_replace("_", " ", $col));
                    
                    if($i == 0){
                            $updatedFields.= $fieldStr .' from '. $oldValue[0] .' To '. $newValue[0];
                    }else{
                        $updatedFields.= ', ' .$fieldStr .' from '. $oldValue[0] .' To '. $newValue[0];                        
                    }
                    $i++;
                }
            }
            $this->logActivity($taxRate->id, 'TaxRate', config('constants.LOG_EVENTS.taxrate_details_updated'), 'TaxRate: '.$taxRate->name.' - '.$updatedFields);
        }
    }

    /**
     * Handle the TaxRate "deleted" event.
     *
     * @param  \App\Models\TaxRate  $taxRate
     * @return void
     */
    public function deleted(TaxRate $taxRate)
    {
        //
    }

    /**
     * Handle the TaxRate "restored" event.
     *
     * @param  \App\Models\TaxRate  $taxRate
     * @return void
     */
    public function restored(TaxRate $taxRate)
    {
        //
    }

    /**
     * Handle the TaxRate "force deleted" event.
     *
     * @param  \App\Models\TaxRate  $taxRate
     * @return void
     */
    public function forceDeleted(TaxRate $taxRate)
    {
        //
    }
}
