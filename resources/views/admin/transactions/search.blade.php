<div class="collapse search-body {{ Route::is('transactions.search') ? 'show' : '' }}" id="collapseExample">
                <div class="card card-body">
                    <form id="search" action="{{ route('transactions.search') }}" enctype="multipart/form-data" method="get">
                        @csrf
                        <input type="hidden" name="page" value="transactions">
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
                                    <label>{{ __('Plan') }}
                                    </label>
                                    <input type="text" name="plan" class="form-control" value="{{ isset($requestedData['plan']) && $requestedData['plan'] != '' ? $requestedData['plan'] : ''}}" placeholder="{{ __('Plan') }}" maxlength="255">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>{{ __('Coupon') }}
                                    </label>
                                    <input type="text" name="coupon" class="form-control" value="{{ isset($requestedData['coupon']) && $requestedData['coupon'] != '' ? $requestedData['coupon'] : ''}}" placeholder="{{ __('Coupon') }}" maxlength="255">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>{{ __('Type') }}
                                    </label>
                                    <input type="text" name="type" class="form-control" value="{{ isset($requestedData['type']) && $requestedData['type'] != '' ? $requestedData['type'] : ''}}" placeholder="{{ __('Type') }}" maxlength="16">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>{{ __('Payment Gateway') }}
                                    </label>
                                    <input type="text" name="gateway" class="form-control" value="{{ isset($requestedData['gateway']) && $requestedData['gateway'] != '' ? $requestedData['gateway'] : ''}}" placeholder="{{ __('Payment Gateway') }}" maxlength="32">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>{{ __('Transaction ID') }}
                                    </label>
                                    <input type="text" name="payment_id" class="form-control" value="{{ isset($requestedData['payment_id']) && $requestedData['payment_id'] != '' ? $requestedData['payment_id'] : ''}}" placeholder="{{ __('Transaction ID') }}" maxlength="128">
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="submit" id="searchSubmit" class="btn btn-primary" disabled>{{ __('Search') }}</button>
                        <button type="button" id="reset" class="btn btn-primary" onclick="window.location='{{ route("admin.transaction") }}'">{{ __('Reset') }}</button>
                    </form>
                </div>
        </div>