<div class="collapse search-body {{ Route::is('plan.search') ? 'show' : '' }}" id="collapseExample">
    <div class="card card-body">
        <form id="search" action="{{ route('plan.search') }}" enctype="multipart/form-data" method="get">
            @csrf
            <input type="hidden" name="page" value="plan">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ __('Name') }}</label>
                        <input type="text" name="name" class="form-control" value="{{ isset($requestedData['name']) && $requestedData['name'] != '' ? $requestedData['name'] : ''}}" placeholder="{{ __('Name') }}" maxlength="255">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ __('Description') }}</label>
                        <input type="text" name="description" class="form-control" value="{{ isset($requestedData['description']) && $requestedData['description'] != '' ? $requestedData['description'] : ''}}" placeholder="{{ __('Description') }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="i-currency">{{ __('Currency') }}</label>
                        <select name="currency" id="i-currency" class="custom-select">
                            <option value="">{{ __('Please Select Option') }}</option>
                            @foreach ($currencies as $key => $value)
                            <option value="{{ $key }}" @if (isset($requestedData['currency']) && $requestedData['currency']==$key) selected @endif>
                                {{ $key }} - {{ $value }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="i-status">{{ __('Status') }}</label>
                        <select name="status" id="i-status" class="custom-select">
                        <option value="">{{ __('Please Select Option') }}</option>
                            <option value="1" {{isset($requestedData['status']) && $requestedData['status'] == '1' ? 'selected' : ''}}>Active</option>
                            <option value="0" {{isset($requestedData['status']) && $requestedData['status'] == '0' ? 'selected' : ''}}>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" name="submit" id="searchSubmit" class="btn btn-primary" disabled>{{ __('Search') }}</button>
            <button type="button" id="reset" class="btn btn-primary" onclick="window.location='{{ route("admin.plans") }}'">{{ __('Reset') }}</button>
        </form>
    </div>
</div>