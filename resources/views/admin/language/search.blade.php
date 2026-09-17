<div class="collapse search-body {{ Route::is('languages.search') ? 'show' : '' }}" id="collapseExample">
    <div class="card card-body">
        <form id="search" action="{{ route('languages.search') }}" enctype="multipart/form-data" method="get">
            @csrf
            <input type="hidden" name="page" value="language">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ __('Code') }}
                        </label>
                        <input type="text" name="code" class="form-control" value="{{ isset($requestedData['code']) && $requestedData['code'] != '' ? $requestedData['code'] : ''}}" placeholder="{{ __('Code') }}" maxlength="64">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ __('Name') }}
                        </label>
                        <input type="text" name="name" class="form-control" value="{{ isset($requestedData['name']) && $requestedData['name'] != '' ? $requestedData['name'] : ''}}" placeholder="{{ __('Name') }}" maxlength="255">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ __('Direction') }}</label>
                        <select name="direction" class="form-control">
                            <option value="">{{ __('Please Select Option') }}</option>
                            <option value="ltr" {{isset($requestedData['direction']) && $requestedData['direction'] == 'ltr' ? 'selected' : ''}}>{{ __('LTR') }}</option>
                            <option value="rtl" {{isset($requestedData['direction']) && $requestedData['direction'] == 'rtl' ? 'selected' : ''}}>{{ __('RTL') }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="i-status">{{ __('Status') }}</label>
                        <select name="status" id="i-status" class="custom-select">
                        <option value="">{{ __('Please Select Option') }}</option>
                            <option value="active" {{isset($requestedData['status']) && $requestedData['status'] == 'active' ? 'selected' : ''}}>{{ __('Active') }}</option>
                            <option value="inactive" {{isset($requestedData['status']) && $requestedData['status'] == 'inactive' ? 'selected' : ''}}>{{ __('Inactive') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" name="submit" id="searchSubmit" class="btn btn-primary" disabled>{{ __('Search') }}</button>
            <button type="button" id="reset" class="btn btn-primary" onclick="window.location='{{ route("languages") }}'">{{ __('Reset') }}</button>
        </form>
    </div>
</div>