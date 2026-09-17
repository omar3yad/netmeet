<div class="collapse search-body {{ Route::is('coupons.search') ? 'show' : '' }}" id="collapseExample">
    <div class="card card-body">
        <form id="search" action="{{ route('coupons.search') }}" enctype="multipart/form-data" method="get">
            @csrf
            <input type="hidden" name="page" value="coupon">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ __('Name') }}</label>
                        <input type="text" name="name" class="form-control" value="{{ isset($requestedData['name']) && $requestedData['name'] != '' ? $requestedData['name'] : ''}}" placeholder="{{ __('Name') }}" maxlength="255">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ __('Code') }}</label>
                        <input type="text" name="code" class="form-control" value="{{ isset($requestedData['code']) && $requestedData['code'] != '' ? $requestedData['code'] : ''}}" placeholder="{{ __('Code') }}" maxlength="255">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                    <label>{{ __('Type') }}</label>
                            <select name="type" id="i-type"
                                class="custom-select">
                                <option value="">{{__('Please Select Option')}}</option>
                                @foreach ([0 => __('Discount'), 1 => __('Redeemable')] as $key => $value)
                                    <option value="{{ $key }}" @if (isset($requestedData['type']) && $requestedData['type'] == $key) selected @endif>
                                        {{ $value }}</option>
                                @endforeach
                            </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="i-status">{{ __('Status') }}</label>
                        <select name="status" id="i-status" class="custom-select">
                            <option value="">{{__('Please Select Option')}}</option>
                            <option value="1" {{isset($requestedData['status']) && $requestedData['status'] == '1' ? 'selected' : ''}}>{{__('Active')}}</option>
                            <option value="0" {{isset($requestedData['status']) && $requestedData['status'] == '0' ? 'selected' : ''}}>{{__('Inactive')}}</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" name="submit" id="searchSubmit" class="btn btn-primary" disabled>{{ __('Search') }}</button>
            <button type="button" id="reset" class="btn btn-primary" onclick="window.location='{{ route("admin.coupons") }}'">{{ __('Reset') }}</button>
        </form>
    </div>
</div>