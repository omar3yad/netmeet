<div class="collapse search-body {{ Route::is('user.search') ? 'show' : '' }}" id="collapseExample">
    <div class="card card-body">
        <form id="search" action="{{ route('user.search') }}" enctype="multipart/form-data" method="get">
            @csrf
            <input type="hidden" name="page" value="user">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ __('Username') }}
                        </label>
                        <input type="text" name="username" class="form-control" value="{{ isset($requestedData['username']) && $requestedData['username'] != '' ? $requestedData['username'] : ''}}" placeholder="{{ __('Username') }}" maxlength="20">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ __('Email') }}
                        </label>
                        <input type="text" name="email" class="form-control" value="{{ isset($requestedData['email']) && $requestedData['email'] != '' ? $requestedData['email'] : ''}}" placeholder="{{ __('Email') }}" maxlength="50">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ __('Created Date') }}
                        </label>
                        <input type="text" class="form-control" name="daterange" value="{{ isset($requestedData['daterange']) && $requestedData['daterange'] != '' ? $requestedData['daterange'] : ''}}" placeholder="{{ __('Created Date') }}" maxlength="50" />
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
            <button type="button" id="reset" class="btn btn-primary" onclick="window.location='{{ route("users") }}'">{{ __('Reset') }}</button>
        </form>
    </div>
</div>