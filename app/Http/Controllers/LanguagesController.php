<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Http\Requests\StoreLanguageRequest;
use App\Http\Requests\UpdateLanguageRequest;

class LanguagesController extends Controller
{
    /**
     * Manage languages
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $languages = Language::orderBy('id', 'DESC')->paginate(config('app.pagination'));

        return view('admin.language.index', [
            'page' => __('Languages'),
            'languages' => $languages,
        ]);
    }

    //search language
    public function searchLanguage(Request $request)
    {
        $languages = Language::select();
        if ($request->input('mid') && $request->input('mid') != '') {
            $languages->where('code', 'like', '%'.$request->input('mid').'%');
        }

        if ($request->input('name') && $request->input('name') != '') {
            $languages->where('name','like', '%'.$request->input('name').'%');
        }

        if ($request->input('direction') != '') {
            $languages->where('direction', $request->input('direction'));
        }

        if ($request->input('status') && $request->input('status') != '') {
            $languages->where('status', $request->input('status'));
        }

        $data = $languages->orderBy('id', 'DESC')->paginate(config('app.pagination'))->appends(request()->query());

        return view('admin.language.index', [
            'page' => __('Languages'),
            'languages' => $data,
            'requestedData' => $request->all()
        ]);
    }

    //return the create page
    public function create()
    {
        return view('admin.language.create', [
            'page' => __('Language'),
        ]);
    }

    //create language and store the file
    public function createLanguage(StoreLanguageRequest $request)
    {
        if (isDemoMode()) return back()->with('error', __('This feature is not available in demo mode'));

        $file = $request->file;

        if ($file && $file->isValid()) {
            if ($request->default == "yes" && $request->status == "inactive") {
                return back()->with('error', __('The default language can not be inactive'));
            }

            if ($request->default == 'yes') {
                Language::where(['default' => 'yes'])->update(['default' => 'no']);
            }

            $model = new Language();
            $model->code = $request->code;
            $model->name = $request->name;
            $model->direction = $request->direction;
            $model->default = $request->default;
            $model->status = $request->status;

            $model->save();
            Cache::forget('languages');
            Cache::forget('defaultLangauage');

            Storage::disk('languages')->put($request->code . '.json', File::get($file));
            $file->storeAs('public/languages', $request->code . '.json');

            return redirect('/admin/languages')->with('success', __('Settings saved.'));
        }
        return back()->with('error', __('The given file is not valid'));
    }

    //return the edit page
    public function edit($id)
    {
        $model = Language::find($id);

        return view('admin.language.edit', [
            'page' => __('Language'),
            'model' => $model,
        ]);
    }

    //update language and update the file if available
    public function updateLanguage(UpdateLanguageRequest $request, $id)
    {
        if (isDemoMode()) return back()->with('error', __('This feature is not available in demo mode'));

        $model = Language::find($id);
        $file = $request->file;

        if ($request->default == "yes" && $request->status == "inactive") {
            return back()->with('error', __('The default language can not be inactive'));
        }

        if ($model->default == "yes" && $request->default == "no") {
            return back()->with('error', __('There must be at least one default language'));
        }

        if ($model->default == 'no' && $request->default == 'yes') {
            Language::where(['default' => 'yes'])->update(['default' => 'no']);
        }

        $model->direction = $request->direction;
        $model->default = $request->default;
        $model->status = $request->status;
        $model->save();
        Cache::forget('languages');
        Cache::forget('defaultLangauage');

        if ($file) {
            Storage::disk('languages')->put($model->code . '.json', File::get($file));
            $file->storeAs('public/languages', $model->code . '.json');
        }

        return redirect('/admin/languages')->with('success', __('Settings saved.'));
    }

    //delete language and file
    public function deleteLanguage(Request $request)
    {
        if (isDemoMode()) return json_encode(['success' => false, 'error' => __('This feature is not available in demo mode')]);

        $model = Language::find($request->id);

        if ($model->code == 'en') {
            return json_encode(['success' => false, 'error' => __('This language can not be deleted')]);
        } else if ($model->default == "yes") {
            return json_encode(['success' => false, 'error' => __('The default language can not be deleted.')]);
        }

        if ($model->delete()) {
            Cache::forget('languages');
            Cache::forget('defaultLangauage');

            Storage::disk('languages')->delete($model->code . '.json');
            Storage::delete('public/languages/' . $model->code . '.json');
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }

    //download sample english file
    public function downloadEnglish()
    {
        return response()->download("sources/en-sample.json");
    }

    //download language file
    public function downloadFile($code)
    {
        return response()->download("storage/languages/" . $code . ".json");
    }
}
