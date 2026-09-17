<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailJob;
use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\EmailTemplates;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use stdClass;
use Illuminate\Support\Facades\App;
use App\Models\Plan;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('tfa', ['except' => ['checkMeeting', 'setLocale']]);
        $this->middleware('auth', ['except' => ['meeting', 'checkMeeting', 'checkMeetingPassword', 'getDetails', 'setLocale', 'widget', 'checkDetails']]);

        if (getSetting('VERIFY_USERS') == 'enabled') {
            $this->middleware('customVerified', ['except' => 'setLocale']);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        //if the user previously selected a plan
        if (!empty($request->session()->get('plan_redirect'))) {
            return redirect()->route('checkout.index', ['id' => $request->session()->get('plan_redirect')['id'], 'interval' => $request->session()->get('plan_redirect')['interval']]);
        }

        $meetings = Meeting::where('user_id', Auth::id())->orderBy('id', 'DESC');

        //search meeting
        if ($request->search) {
            $meetings->where('title', 'like' , "%" . $request->search . "%")->orWhere('description', 'like', "%" . $request->search . "%");
        }

        $meetings = $meetings->paginate(10);

        // Get the selected meeting if specified in URL
        $selectedMeeting = null;
        if ($request->has('meeting')) {
            $selectedMeeting = Meeting::where('user_id', Auth::id())
                ->where('id', $request->meeting)
                ->first();
        }

        // Use selected meeting if available, otherwise use first meeting
        $displayMeeting = $selectedMeeting ?: (!$meetings->isEmpty() ? $meetings[0] : null);

        $contacts = Auth::user()->contact;

        return view('dashboard', [
            'page' => __('Dashboard'),
            'meetings' => $meetings,
            'firstMeeting' => $displayMeeting,
            'selectedMeetingId' => $request->meeting,
            'allMeetings' => Meeting::where('user_id', Auth::id())->orderBy('id', 'DESC')->get(),
            'timezones' => json_decode(file_get_contents(public_path() . '/sources/timezones.json'), true),
            'timeLimit' => getUserPlanFeatures(Auth::id())->time_limit,
            'contacts' => $contacts,
            'search' => $request->search
        ]);
    }

    //create a new meeting
    public function createMeeting(Request $request)
    {
        $request->validate([
            'meeting_id' => 'required',
            'title' => 'required|max:100',
            'description' => 'max:1000',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required',
        ]);

        $allowedMeetings = getUserPlanFeatures(Auth::id())->meeting_no;
        if ($allowedMeetings != -1 && count(Auth::user()->meeting) >= $allowedMeetings) {
            return json_encode(['success' => false, 'error' => __('You have reached the maximum meeting creation limit. Upgrade now')]);
        }
        
        $meeting = new Meeting();
        $meeting->meeting_id = $request->meeting_id;
        $meeting->title = $request->title;
        $meeting->description = $request->description;
        $meeting->user_id = Auth::id();
        $meeting->password = $request->password;
        $meeting->date = $request->date;
        $meeting->time = $request->time;
        $meeting->timezone = $request->timezone;

        if ($meeting->save()) {
            $meeting->date = formatDate($meeting->date);
            $meeting->time = formatTime($meeting->time);
            return json_encode(['success' => true, 'data' => $meeting]);
        }

        return json_encode(['success' => false]);
    }

    // Get all meetings for the Others modal
    public function getAllMeetings()
    {
        \Log::info('getAllMeetings method called');
        
        $meetings = Meeting::where('user_id', Auth::id())
            ->orderBy('id', 'DESC')
            ->get(['id', 'title', 'description', 'meeting_id', 'date', 'time', 'timezone']);

        \Log::info('Found meetings:', ['count' => $meetings->count()]);

        return response()->json([
            'success' => true,
            'meetings' => $meetings
        ]);
    }

    // Get a specific meeting by ID
    public function getMeetingById($id)
    {
        $meeting = Meeting::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if (!$meeting) {
            return response()->json([
                'success' => false,
                'message' => 'Meeting not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'meeting' => $meeting
        ]);
    }

    //delete a meeting
    public function deleteMeeting(Request $request)
    {
        $meeting = Meeting::find($request->id);

        if ($meeting->delete()) {
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }

    //edit a meeting
    public function editMeeting(Request $request)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $meeting = Meeting::find($request->id);
        $meeting->title = $request->title;
        $meeting->description = $request->description;
        $meeting->password = $request->password;
        $meeting->date = $request->date;
        $meeting->time = $request->time;
        $meeting->timezone = $request->timezone;

        if ($meeting->save()) {
            $meeting->date = formatDate($meeting->date);
            $meeting->time = formatTime($meeting->time);
            return json_encode(['success' => true, 'data' => $meeting]);
        }

        return json_encode(['success' => false]);
    }

    //send an email invite
    public function sendInvite(Request $request)
    {
        $newEmails = json_decode($request->emails);
        $meeting = Meeting::find($request->id);
        $oldEmails = explode(',', $meeting->invites);
        $allEmails = array_unique(array_merge($oldEmails, $newEmails), SORT_REGULAR);
        $meeting->invites = implode(',', $allEmails);

        $emailBody = EmailTemplates::where('slug','meeting-invitation')->first();
        
        if ($meeting->save()) {
            dispatch(new SendEmailJob($meeting, $newEmails, $emailBody['content']));
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }

    //get all the invites associated with the meeting
    public function getInvites(Request $request)
    {
        $meeting = Meeting::find($request->id);

        if ($meeting) {
            return json_encode(['success' => true, 'data' => $meeting->invites ? explode(',', $meeting->invites) : []]);
        }

        return json_encode(['success' => false]);
    }

    //check if the meeting exist
    public function checkMeeting(Request $request)
    {
        if (getSetting('AUTH_MODE') == 'disabled') {
            return json_encode(['success' => true, 'id' => $request->id]);
        }

        $meeting = Meeting::where(['meeting_id' => $request->id, 'status' => 'active'])->first();
        $user = User::where(['username' => $request->id, 'status' => 'active'])->first();

        if ($meeting || $user) {
            return json_encode(['success' => true, 'id' => $request->id]);
        }

        return json_encode(['success' => false]);
    }

    /**
     * Show the meeting page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function meeting($id)
    {
        $meeting = new \stdClass();

        if (getSetting('AUTH_MODE') == 'enabled') {
            $meeting = Meeting::where(['meeting_id' => $id, 'status' => 'active'])->first();
            $user = User::where(['username' => $id, 'status' => 'active'])->first();

            if (!$meeting && !$user) {
                return redirect('/')->withErrors(__('The meeting does not exist'));
            }

            //for personal meeting link
            if ($user) {
                $allowedMeetings = getUserPlanFeatures($user->id)->meeting_no;
                if ($allowedMeetings != -1 && count($user->meeting) >= $allowedMeetings) {
                    if (Auth::check()) {
                        return redirect()->route('dashboard')->withErrors(__('You have reached the maximum meeting creation limit. Upgrade now'));
                    } else {
                        return redirect('/')->withErrors(__('The meeting does not exist'));
                    }
                }

                $meeting = new \stdClass();
                $meeting->title = __('Personal Meeting');
                $meeting->user_id = $user->id;
                $meeting->meeting_id = $id;
                $meeting->date = '';
                $meeting->time = '';
                $meeting->timezone = '';
                $meeting->description = '';
                $meeting->password = '';
            }

            $meeting->features = getUserPlanFeatures($meeting->user_id);
        } else {
            $meeting->title = __('Meeting');
            $meeting->meeting_id = $id;
            $meeting->description = '-';
            $meeting->password = null;
            $meeting->user_id = 0;
            $meeting->features = Plan::first()->features;
        }

        $meeting->isModerator = Auth::user() && getSetting('MODERATOR_RIGHTS') == "enabled"  ? Auth::user()->id == $meeting->user_id : false;
        $meeting->username = Auth::user() ? Auth::user()->username : '';
        $meeting->timeLimit = isDemoMode() ? 10 : $meeting->features->time_limit;
        $meeting->userLimit = $meeting->features->user_limit ?? 5;
        $meeting->isAdmin = Auth::user() && getSetting('MODERATOR_RIGHTS') == "enabled"  ? Auth::user()->role == 'admin' : false;

        return view('meeting', [
            'page' => __('Meeting'),
            'meeting' => $meeting
        ]);
    }

    //check if meeting password is valid or not
    public function checkMeetingPassword(Request $request)
    {
        $meeting = Meeting::find($request->id);

        if ($meeting->password == $request->password) {
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }

    //get the application details and send it to the user
    public function getDetails()
    {
        $details = new stdClass();
        $details->stunUrl = getSetting('STUN_URL');
        $details->turnUrl = getSetting('TURN_URL');
        $details->turnUsername = getSetting('TURN_USERNAME');
        $details->turnPassword = getSetting('TURN_PASSWORD');
        $details->defaultUsername = getSetting('DEFAULT_USERNAME');
        $details->appName = getSetting('APPLICATION_NAME');
        $details->signalingURL = getSetting('SIGNALING_URL');
        $details->authMode = getSetting('AUTH_MODE');
        $details->moderatorRights = getSetting('MODERATOR_RIGHTS');
        $details->endURL = getSetting('END_URL');
        $details->limitedScreenShare = getSetting('LIMITED_SCREEN_SHARE');

        return json_encode(['success' => true, 'data' => $details]);
    }

    //set locale in the session
    public function setLocale(Request $request)
    {
        $locale = $request->locale;
        session(['locale' => $locale]);
        App::setLocale($locale);

        return redirect()->back();
    }

    //show widget for whiteboard
    public function widget()
    {
        return view('widget');
    }

    /* //create a meeting and redirect to meeting page
    public function instant(Request $request)
    {
        $allowedMeetings = getUserPlanFeatures(Auth::id())->meeting_no;
        if ($allowedMeetings != -1 && count(Auth::user()->meeting) >= $allowedMeetings) {
            return redirect()->route('dashboard')->withErrors(__('You have reached the maximum meeting creation limit. Upgrade now'));
        }

        $meeting = new Meeting();
        $meeting->meeting_id = $request->id;
        $meeting->title = __('Meeting') . ' ' . $request->date;
        $meeting->user_id = Auth::id();

        $meeting->save();
        return redirect('/meeting/'. $meeting->meeting_id);
    } */

    //check details
    public function checkDetails() {
        $license_notifications_array = aplVerifyLicense('', true);
        
        if ($license_notifications_array['notification_case'] == "notification_license_ok") {
            return true;
        } else {
            return false;
        }
    }
}
