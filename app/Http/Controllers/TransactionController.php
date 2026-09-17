<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TransactionController extends Controller
{
    //list all the transactions
    public function index()
    {
        $payment = Payment::orderBy('id', 'DESC')->paginate(config('app.pagination'));

        return view('admin.transactions.transaction', ['page' => __('Transaction'), 'transaction' => $payment]);
    }

    // search transaction
    public function searchTransactions(Request $request)
    {
        $payment = Payment::select('payments.*','users.username')->join('users', 'payments.user_id', 'users.id');

        if ($request->input('username') && $request->input('username') != '') {
            $payment->where('username', 'like', '%'.$request->input('username').'%');
        }

        if ($request->input('plan') && $request->input('plan') != '') {
            $payment->where('product', 'like', '%'.$request->input('plan').'%');
        }

        if ($request->input('coupon') && $request->input('coupon') != '') {
            $payment->where('coupon', 'like', '%'.$request->input('coupon').'%');
        }

        if ($request->input('type') && $request->input('type') != '') {
            $payment->where('interval', 'like', '%'.$request->input('type').'%');
        }

        if ($request->input('gateway') && $request->input('gateway') != '') {
            $payment->where('gateway', 'like', '%'.$request->input('gateway').'%');
        }

        if ($request->input('payment_id') && $request->input('payment_id') != '') {
            $payment->where('payment_id', 'like', '%'.$request->input('payment_id').'%');
        }

        $data = $payment->orderBy('id', 'DESC')->paginate(config('app.pagination'))->appends(request()->query());

        return view('admin.transactions.transaction', [
            'page' => __('Transaction'),
            'transaction' => $data,
            'requestedData' => $request->all()
        ]);   
    }

    //export transaction
    public function exportTransaction(Request $request){
        try{
            $payment = Payment::select('payments.payment_id','payments.product','payments.amount','payments.currency','payments.interval','users.username','payments.gateway','payments.coupon','payments.tax_rates')
            ->join('users', 'payments.user_id', 'users.id')->orderBy('payments.id', 'DESC');

            if ($request->input('username') && $request->input('username') != '') {
                $payment->where('username', 'like', '%'.$request->input('username').'%');
            }
    
            if ($request->input('plan') && $request->input('plan') != '') {
                $payment->where('product', 'like', '%'.$request->input('plan').'%');
            }
    
            if ($request->input('coupon') && $request->input('coupon') != '') {
                $payment->where('coupon', 'like', '%'.$request->input('coupon').'%');
            }
    
            if ($request->input('type') && $request->input('type') != '') {
                $payment->where('interval', 'like', '%'.$request->input('type').'%');
            }
    
            if ($request->input('gateway') && $request->input('gateway') != '') {
                $payment->where('gateway', 'like', '%'.$request->input('gateway').'%');
            }
    
            if ($request->input('payment_id') && $request->input('payment_id') != '') {
                $payment->where('payment_id', 'like', '%'.$request->input('payment_id').'%');
            }


            $payment = $payment->get();
            if(count($payment) > 0){
                foreach($payment as $key => $pay){
                    $payment[$key]['product'] = $payment[$key]['product']->name;
                    if($payment[$key]['coupon']){
                        $payment[$key]['coupon'] = $payment[$key]['coupon']->name;
                    }
                    if($payment[$key]['tax_rates']){
                        $payment[$key]['tax_rates'] = $payment[$key]['tax_rates']->name;
                    }
                    
                }
            }
            $csvFileName = 'transactions.csv';
            $result = exportToCSV($payment,$csvFileName);
            return Response::make('', 200, $result);
        }catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }        
    }
}
