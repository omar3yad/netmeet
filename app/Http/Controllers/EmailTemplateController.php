<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplates;
use DataTables;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateEmailTemplatesRequest;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\LogActivity;

class EmailTemplateController extends Controller
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
        $pages = EmailTemplates::orderBy('id', 'DESC')->paginate(config('app.pagination'));

        return view('admin.email-templates.index', [
            'page' => __('Email Templates'),
            'pages' => $pages,
        ]);
        
    }

    /**
     * edit
     *
     * @param  mixed $request
     * @return object
     */
    public function edit($id)
    {
        $emailTemplate = EmailTemplates::findOrFail($id);
        return view('admin.email-templates.edit',['page' => __('Email Template'),'data'=>$emailTemplate]);
    }

    /**
     * update email templates
     *
     * @param  mixed $request
     * * @param  mixed $id
     * @return object
     */
    public function updateEmailTemplate(UpdateEmailTemplatesRequest $request, $id)
    {
        if (isDemoMode()) return back()->with('error', __('This feature is not available in demo mode'));
        
        $model = EmailTemplates::find($id);
        if($request->name != $model->name && $request->content != $model->content){
            $updatedFields = 'Email content updated and name from '.$model->name.' to '.$request->name;
            logActivity($id, 'EmailTemplates', config('constants.LOG_EVENTS.email_templates_updated'), $updatedFields);
        }elseif($request->name != $model->name){
            $updatedFields = 'Name from '.$model->name.' to '.$request->name;
            logActivity($id, 'EmailTemplates', config('constants.LOG_EVENTS.email_templates_updated'), $updatedFields);
        }elseif($request->content != $model->content){
            $updatedFields = 'Name:'.$model->name.' - Email content updated';
            logActivity($id, 'EmailTemplates', config('constants.LOG_EVENTS.email_templates_updated'), $updatedFields);
        }
        $model->name = $request->name;
        $model->content = $request->content;
        $model->save();

        return redirect('/admin/email-templates')->with('success', __('Successfully updated'));
    }
}
