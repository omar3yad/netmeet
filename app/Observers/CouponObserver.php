<?php

namespace App\Observers;

use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CouponObserver extends BaseObserver
{
    /**
     * Handle the Coupon "created" event.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return void
     */
    public function created(Coupon $coupon)
    {
        $requestData = request()->all();
        $this->logActivity($coupon->id, 'Coupon', config('constants.LOG_EVENTS.coupon_created'), 'Name:'.$requestData['name']);
    }

    /**
     * Handle the Coupon "updated" event.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return void
     */
    public function updated(Coupon $coupon)
    {
        if(request()->has('checked')){
            if(request()->has('checked') && request()->get('checked') == "true"){
                $status = 'Name:'.$coupon->name.' - from inactive to active';
            }else{
                $status = 'Name:'.$coupon->name.' - from active to inactive';
            }
            $this->logActivity($coupon->id, 'Coupon', config('constants.LOG_EVENTS.coupon_status_updated'), $status);
        }else{
            $columns = Schema::getColumnListing('coupons');

            $updatedFields = '';
            $i = 0;
            foreach ($columns as $col) {
                if ($coupon->isDirty($col) && $col != 'updated_at') { 
                    $newValue = $coupon->{$col};
                    $oldValue = $coupon->getOriginal($col);
                    $fieldStr = ucwords(str_replace("_", " ", $col));
                    
                    if($i == 0){
                            $updatedFields.= $fieldStr .' from '. $oldValue .' To '. $newValue;
                    }else{
                        $updatedFields.= ', ' .$fieldStr .' from '. $oldValue .' To '. $newValue;                        
                    }
                    $i++;
                }
            }
            $this->logActivity($coupon->id, 'Coupon', config('constants.LOG_EVENTS.coupon_details_updated'), 'Name: '.$coupon->name.' - '.$updatedFields);
        }
    }

    /**
     * Handle the Coupon "deleted" event.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return void
     */
    public function deleted(Coupon $coupon)
    {
        //
    }

    /**
     * Handle the Coupon "restored" event.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return void
     */
    public function restored(Coupon $coupon)
    {
        //
    }

    /**
     * Handle the Coupon "force deleted" event.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return void
     */
    public function forceDeleted(Coupon $coupon)
    {
        //
    }
}
