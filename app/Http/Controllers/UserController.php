<?php

namespace App\Http\Controllers;

use App\Mail\UserCreation;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Plan;
use App\Models\EmailTemplates;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\StoreUserRequest;

class UserController extends Controller
{
    /**
     * Show all the users.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $users = User::select('users.id', 'username', 'email', 'users.status', 'users.plan_id', 'users.created_at', 'plans.name', 'users.facebook_id', 'users.twitter_id', 'users.google_id', 'users.linkedin_id')
            ->where('role', 'end-user')
            ->join('plans', 'users.plan_id', 'plans.id')
            ->orderBy('id', 'DESC')
            ->paginate(config('app.pagination'));
            
        $plans = Plan::active()->get()->pluck('id','name');
            return view('admin.user.index', [
                'page' => __('Users'),
                'users' => $users,
                'plans' => $plans
            ]);
    }

    //export user
    public function exportUser(Request $request){
        if (isDemoMode()) return back()->with('error', __('This feature is not available in demo mode'));
        
        try{
            $users = User::select('username', 'email', 'users.status', 'users.created_at', 'plans.name  as Plan')
            ->where('role', 'end-user')
            ->join('plans', 'users.plan_id', 'plans.id');
        
            if ($request->input('username') && $request->input('username') != '') {
                $users->where('username', 'like', '%'.$request->input('username').'%');
            }

            if ($request->input('email') && $request->input('email') != '') {
                $users->where('email', 'like', '%'.$request->input('email').'%');
            }

            if ($request->input('status') && $request->input('status') != '') {
                $users->where('users.status', 'like', '%'.$request->input('status').'%');
            }

            if ($request->input('daterange') && $request->input('daterange') != '') {
                $dates = explode("/",$request->input('daterange'));
                $dateRangeStart = $dates[0].' 00:00:00';
                $dateRangeEnd = $dates[1].' 23:59:59';
                $users->whereBetween("users.created_at", [$dateRangeStart, $dateRangeEnd]);
            }

            $users = $users->orderBy('users.id', 'DESC')->get();
            $csvFileName = 'users.csv';
            $result = exportToCSV($users,$csvFileName);
            return Response::make('', 200, $result);
        }catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }        
    }


    //search user
    public function searchUser(Request $request)
    {
        $users = User::select('users.id', 'username', 'email', 'users.status', 'users.created_at', 'plans.name')
            ->where('role', 'end-user')
            ->join('plans', 'users.plan_id', 'plans.id');
        $plans = Plan::active()->get()->pluck('id','name');
        if ($request->input('username') && $request->input('username') != '') {
            $users->where('username', 'like', '%'.$request->input('username').'%');
        }

        if ($request->input('email') && $request->input('email') != '') {
            $users->where('email', 'like', '%'.$request->input('email').'%');
        }

        if ($request->input('status') && $request->input('status') != '') {
            $users->where('users.status', 'like', '%'.$request->input('status').'%');
        }

        if ($request->input('daterange') && $request->input('daterange') != '') {
            $dates = explode("/",$request->input('daterange'));
            $dateRangeStart = $dates[0].' 00:00:00';
            $dateRangeEnd = $dates[1].' 23:59:59';
            $users->whereBetween("users.created_at", [$dateRangeStart, $dateRangeEnd]);
        }

        $data = $users->orderBy('id', 'DESC')->paginate(config('app.pagination'))->appends(request()->query());

        return view('admin.user.index', [
            'page' => __('Users'),
            'users' => $data,
            'plans' => $plans,
            'requestedData' => $request->all()
        ]);    
    }

    //udpate user status and return json data
    public function updateUserStatus(Request $request)
    {
        if (isDemoMode()) return json_encode(['success' => false, 'error' => __('This feature is not available in demo mode')]);

        $user = User::find($request->id);
        $user->status = $request->checked == 'true' ? 'active' : 'inactive';

        if ($user->save()) {
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }

    //delete user and return json data
    public function deleteUser(Request $request)
    {
        if (isDemoMode()) return json_encode(['success' => false, 'error' => __('This feature is not available in demo mode')]);

        $user = User::find($request->id);

        if ($user->delete()) {
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }

    //show create user form
    public function createUserForm()
    {
        return view('admin.user.create', [
            'page' => __('Create User'),
        ]);
    }

    //create user and send an email
    public function createUser(StoreUserRequest $request)
    {
        $model = new User();
        $model->username = $request->username;
        $model->email = $request->email;
        $model->password = Hash::make($request->password);
        $model->save();

        $emailBody = EmailTemplates::where('slug','user-creation')->first();

        Mail::to($request->email)->send(new UserCreation($request->all(),$emailBody['content']));
        if (getSetting('VERIFY_USERS') == 'enabled') {
            $model->sendEmailVerificationNotification();
        }
        return redirect()->route('users')->with('success', __('User created'));
    }

    // assign plan to user
    public function assignPlan(Request $request){
        $user = User::find($request->user_id);
        $user->plan_id = $request->plan_id;
        if ($user->save()) {
            return json_encode(['success' => true, 'message' => __('Plan Assigned Successfully')]);
        }

        return json_encode(['success' => false, 'message' => __('Something Went Wrong.')]);
    }
}
