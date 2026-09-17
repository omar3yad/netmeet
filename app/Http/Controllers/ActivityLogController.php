<?php

namespace App\Http\Controllers;

use App\Models\LogActivity;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Response;

class ActivityLogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the list page.
    **/
    public function index(Request $request): object
    {
        $logs = LogActivity::select('logs.id','logs.primary_id','logs.user_id','logs.model','logs.event_type','logs.log','logs.ip','logs.created_at','whom.username as whom')
            ->join('users as whom', 'whom.id', 'logs.user_id')
            ->orderBy('id', 'DESC')
            ->paginate(config('app.pagination'));

        return view('admin.activity-log.index', [
            'page' => __('Activity Logs'),
            'logs' => $logs,
        ]);
        
    }

    //search logs
    public function searchActivityLog(Request $request){
        try{
            $logs = LogActivity::select('logs.id','logs.primary_id','logs.user_id','logs.model','logs.event_type','logs.log','logs.ip','logs.created_at','whom.username as whom')
            ->join('users as whom', 'whom.id', 'logs.user_id');

            if ($request->input('uid') && $request->input('uid') != '') {
                
                $logs->where('whom.username', 'like', '%'.$request->input('uid').'%');
            }

            if ($request->input('module') && $request->input('module') != '') {
                $logs->where('logs.model', 'like', '%'.$request->input('module').'%');
            }

            if ($request->input('etype') && $request->input('etype') != '') {
                $logs->where('event_type',$request->input('etype'));
            }

            if ($request->input('log') && $request->input('log') != '') {
                $logs->where('log', 'like', '%'.$request->input('log').'%');
            }

            if ($request->input('ip') && $request->input('ip') != '') {
                $logs->where('ip', 'like', '%'.$request->input('ip').'%');
            }

            if ($request->input('daterange') && $request->input('daterange') != '') {
                $dates = explode("/",$request->input('daterange'));
                $dateRangeStart = $dates[0].' 00:00:00';
                $dateRangeEnd = $dates[1].' 23:59:59';
                $logs->whereBetween("logs.created_at", [$dateRangeStart, $dateRangeEnd]);
            }
                
            $logs = $logs->orderBy('id', 'DESC')->paginate(config('app.pagination'))->appends(request()->query());

            return view('admin.activity-log.index', [
                'page' => __('Activity Logs'),
                'logs' => $logs,
                'requestedData' => $request->all()
            ]);    
        }catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }        
    }

    //export logs
    public function exportActivityLog(Request $request){
        try{
            $logs = LogActivity::select('logs.id','logs.primary_id','whom.username as whom','logs.model','logs.event_type','logs.log','logs.ip','logs.created_at')
            ->join('users as whom', 'whom.id', 'logs.user_id');
    

            if ($request->input('uid') && $request->input('uid') != '') {
                
                $logs->where('whom.username', 'like', '%'.$request->input('uid').'%');
            }

            if ($request->input('module') && $request->input('module') != '') {
                $logs->where('model', 'like', '%'.$request->input('module').'%');
            }

            if ($request->input('etype') && $request->input('etype') != '') {
                $logs->where('event_type',$request->input('etype'));
            }

            if ($request->input('ip') && $request->input('ip') != '') {
                $logs->where('ip', 'like', '%'.$request->input('ip').'%');
            }

            if ($request->input('log') && $request->input('log') != '') {
                $logs->where('log', 'like', '%'.$request->input('log').'%');
            }

            if ($request->input('daterange') && $request->input('daterange') != '') {
                $dates = explode("/",$request->input('daterange'));
                $dateRangeStart = $dates[0].' 00:00:00';
                $dateRangeEnd = $dates[1].' 23:59:59';
                $logs->whereBetween("logs.created_at", [$dateRangeStart, $dateRangeEnd]);
            }
               
            $logs = $logs->orderBy('logs.id', 'DESC')->get();
            $csvFileName = 'activity-log.csv';
            $result = exportToCSV($logs,$csvFileName);
            return Response::make('', 200, $result);
        }catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }        
    }

    
}
