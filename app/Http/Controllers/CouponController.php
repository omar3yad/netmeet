<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\StoreCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Models\Coupon;

class CouponController extends Controller
{
    /**
     * List the Coupons.
     */
    public function index(Request $request)
    {
        $coupons = Coupon::orderBy('id', 'DESC')->paginate(config('app.pagination'));
    
        return view('admin.coupons.index', [ 'page' => __('Coupons'), 'coupons' => $coupons]);
    }

    //search coupon
    public function searchCoupon(Request $request)
    {
        $coupons = Coupon::select();
        if ($request->input('name') && $request->input('name') != '') {
            $coupons->where('name', 'like', '%'.$request->input('name').'%');
        }

        if ($request->input('code') && $request->input('code') != '') {
            $coupons->where('code', 'like', '%'.$request->input('code').'%');
        }

        if ($request->input('type') && $request->input('type') != '') {
            $coupons->where('type',$request->input('type'));
        }

        if($request->input('status') != ''){
            $coupons->where('status',$request->input('status'));
        }
        
        $data = $coupons->orderBy('id', 'DESC')->paginate(config('app.pagination'))->appends(request()->query());

        return view('admin.coupons.index', [
            'page' => __('Coupons'),
            'coupons' => $data,
            'requestedData' => $request->all()
        ]);    
    }


    /**
     * Show the create Coupon form.
     */
    public function create()
    {
        return view('admin.coupons.new', ['page' => __('Create Coupon')]);
    }

    /**
     * Show the edit Coupon form.
     */
    public function edit($id)
    {
        $coupon = Coupon::where('id', $id)->withTrashed()->firstOrFail();

        return view('admin.coupons.edit', ['page' => __('Edit Coupon'), 'coupon' => $coupon]);
    }

    /**
     * Store the Coupon.
     */
    public function store(StoreCouponRequest $request)
    {
        $coupon = new Coupon;

        $coupon->name = $request->input('name');
        $coupon->code = $request->input('code');
        $coupon->type = $request->input('type');
        $coupon->days = $request->input('days');
        $coupon->percentage = $request->input('type') ? 100 : $request->input('percentage');
        $coupon->quantity = $request->input('quantity');

        $coupon->save();

        return redirect()->route('admin.coupons')->with('success', __('Data created successfully'));
    }

    /**
     * Update the Coupon.
     */
    public function update(UpdateCouponRequest $request, $id)
    {
        $coupon = Coupon::withTrashed()->findOrFail($id);

        $coupon->code = $request->input('code');
        $coupon->days = $request->input('days');
        $coupon->quantity = $request->input('quantity');

        $coupon->save();

        return redirect()->route('admin.coupons')->with('success', __('Data updated successfully'));
    }

    //udpate coupon status
    public function updateStatus(Request $request)
    {
        $coupon = Coupon::find($request->id);
        $coupon->status = $request->checked == 'true' ? 1 : 0;

        if ($coupon->save()) {
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }
}
