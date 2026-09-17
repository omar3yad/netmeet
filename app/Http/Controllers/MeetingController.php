<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;
use Illuminate\Support\Facades\Response;

class MeetingController extends Controller
{
    /**
     * Show all the meetings.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $meetings = Meeting::select('meetings.*', 'users.username')
            ->join('users', 'meetings.user_id', 'users.id')
            ->orderBy('id', 'DESC')
            ->paginate(config('app.pagination'));

        return view('admin.meeting.index', [
            'page' => __('Meetings'),
            'meetings' => $meetings,
        ]);
    }

    //search meeting
    public function searchMeeting(Request $request)
    {
        $meetings = Meeting::select('meetings.*', 'users.username')
        ->join('users', 'meetings.user_id', 'users.id');
        if ($request->input('mid') && $request->input('mid') != '') {
            $meetings->where('meetings.meeting_id', 'like', '%'.$request->input('mid').'%');
        }

        if ($request->input('title') && $request->input('title') != '') {
            $meetings->where('meetings.title','like', '%'.$request->input('title').'%');
        }

        if ($request->input('description') && $request->input('description') != '') {
            $meetings->where('meetings.description','like', '%'.$request->input('description').'%');
        }

        if ($request->input('username') && $request->input('username') != '') {
            $meetings->where('users.username','like', '%'.$request->input('username').'%');
        }

        if ($request->input('status') && $request->input('status') != '') {
            $meetings->where('meetings.status','like', '%'.$request->input('status').'%');
        }

        if ($request->input('daterange') && $request->input('daterange') != '') {
            $dates = explode("/",$request->input('daterange'));
            $dateRangeStart = $dates[0].' 00:00:00';
            $dateRangeEnd = $dates[1].' 23:59:59';
            $meetings->whereBetween("meetings.created_at", [$dateRangeStart, $dateRangeEnd]);
        }

        $data = $meetings->orderBy('id', 'DESC')->paginate(config('app.pagination'))->appends(request()->query());

        return view('admin.meeting.index', [
            'page' => __('Meetings'),
            'meetings' => $data,
            'requestedData' => $request->all()
        ]);
    }

    //export meeting
    public function exportMeeting(Request $request){
        try{
            $meetings = Meeting::select('meetings.meeting_id','meetings.title','meetings.description','meetings.password','meetings.status','meetings.date','meetings.time', 'meetings.timezone','users.username','meetings.created_at')
            ->join('users', 'meetings.user_id', 'users.id')->orderBy('meetings.id', 'DESC');

            if ($request->input('mid') && $request->input('mid') != '') {
                $meetings->where('meetings.meeting_id', 'like', '%'.$request->input('mid').'%');
            }

            if ($request->input('title') && $request->input('title') != '') {
                $meetings->where('meetings.title','like', '%'.$request->input('title').'%');
            }

            if ($request->input('description') && $request->input('description') != '') {
                $meetings->where('meetings.description','like', '%'.$request->input('description').'%');
            }

            if ($request->input('username') && $request->input('username') != '') {
                $meetings->where('users.username','like', '%'.$request->input('username').'%');
            }

            if ($request->input('status') && $request->input('status') != '') {
                $meetings->where('meetings.status','like', '%'.$request->input('status').'%');
            }

            if ($request->input('daterange') && $request->input('daterange') != '') {
                $dates = explode("/",$request->input('daterange'));
                $dateRangeStart = $dates[0].' 00:00:00';
                $dateRangeEnd = $dates[1].' 23:59:59';
                $meetings->whereBetween("meetings.created_at", [$dateRangeStart, $dateRangeEnd]);
            }

            $meetings = $meetings->get();
            $csvFileName = 'meetings.csv';
            $result = exportToCSV($meetings, $csvFileName);
            return Response::make('', 200, $result);
        }catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }        
    }

    //udpate meeting status and return json
    public function updateMeetingStatus(Request $request)
    {
        if (isDemoMode()) return json_encode(['success' => false, 'error' => __('This feature is not available in demo mode')]);

        $meeting = Meeting::find($request->id);
        $meeting->status = $request->checked == 'true' ? 'active' : 'inactive';

        if ($meeting->save()) {
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }

    //delete meeting and return json
    public function deleteMeeting(Request $request)
    {
        if (isDemoMode()) return json_encode(['success' => false, 'error' => __('This feature is not available in demo mode')]);

        $meeting = Meeting::find($request->id);

        if ($meeting->delete()) {
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }
}
