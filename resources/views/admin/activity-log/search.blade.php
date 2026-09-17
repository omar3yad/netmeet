<div class="collapse search-body {{ Route::is('activity-log.search') ? 'show' : '' }}" id="collapseExample">
    <div class="card card-body">
        <form id="search" action="{{ route('activity-log.search') }}" enctype="multipart/form-data" method="get">
            @csrf
            <input type="hidden" name="page" value="activitylog">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ __('User') }}
                        </label>
                        <input type="text" name="uid" class="form-control"
                            value="{{ isset($requestedData['uid']) && $requestedData['uid'] != '' ? $requestedData['uid'] : '' }}"
                            placeholder="{{ __('User') }}" maxlength="30">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="i-module">{{ __('Module') }}</label>
                        @php
                            $modulesArray = [
                                'User',
                                'Meeting',
                                'Plan',
                                'Coupon',
                                'TaxRate',
                                'GlobalConfig',
                                'Contact',
                                'Payment',
                            ];
                        @endphp
                        <select name="module" id="i-module" class="custom-select">
                            <option value="">{{ __('Please Select Option') }}</option>
                            @foreach ($modulesArray as $val)
                                <option value="{{ $val }}"
                                    {{ isset($requestedData['module']) && $requestedData['module'] == $val ? 'selected' : '' }}>
                                    {{ __($val) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">

                @php
                    $eventTypes = config('constants.LOG_EVENTS');
                @endphp
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="i-etype">{{ __('Event Type') }}</label>
                        <select name="etype" id="i-etype" class="custom-select">
                            <option value="">{{ __('Please Select Option') }}</option>
                            @foreach ($eventTypes as $key => $val)
                                <option value="{{ $val }}"
                                    {{ isset($requestedData['etype']) && $requestedData['etype'] == $val ? 'selected' : '' }}>
                                    {{ $val }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ __('Created Date') }}
                        </label>
                        <input type="text" class="form-control" name="daterange"
                            value="{{ isset($requestedData['daterange']) && $requestedData['daterange'] != '' ? $requestedData['daterange'] : '' }}"
                            placeholder="{{ __('Created Date') }}" maxlength="50">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ __('IP') }}
                        </label>
                        <input type="text" class="form-control" name="ip"
                            value="{{ isset($requestedData['ip']) && $requestedData['ip'] != '' ? $requestedData['ip'] : '' }}"
                            placeholder="{{ __('IP') }}" maxlength="20">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ __('Log') }}
                        </label>
                        <input type="text" class="form-control" name="log"
                            value="{{ isset($requestedData['log']) && $requestedData['log'] != '' ? $requestedData['log'] : '' }}"
                            placeholder="{{ __('Log') }}" maxlength="50">
                    </div>
                </div>
            </div>

            <button type="submit" name="submit" id="searchSubmit" class="btn btn-primary"
                disabled>{{ __('Search') }}</button>
            <button type="button" id="reset" class="btn btn-primary"
                onclick="window.location='{{ route('activity-log') }}'">{{ __('Reset') }}</button>
        </form>
    </div>
</div>
