<div class="collapse search-body {{ Route::is('meeting.search') ? 'show' : '' }}" id="collapseExample">
                <div class="card card-body">
                    <form id="search" action="{{ route('meeting.search') }}" enctype="multipart/form-data" method="get">
                        @csrf
                        <input type="hidden" name="page" value="meeting">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>{{ __('Meeting ID') }}
                                    </label>
                                    <input type="text" name="mid" class="form-control" value="{{ isset($requestedData['mid']) && $requestedData['mid'] != '' ? $requestedData['mid'] : ''}}" placeholder="{{ __('Meeting ID') }}" maxlength="9">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>{{ __('Title') }}
                                    </label>
                                    <input type="text" name="title" class="form-control" value="{{ isset($requestedData['title']) && $requestedData['title'] != '' ? $requestedData['title'] : ''}}" placeholder="{{ __('Title') }}" maxlength="100">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>{{ __('Description') }}
                                    </label>
                                    <input type="text" name="description" class="form-control" value="{{ isset($requestedData['description']) && $requestedData['description'] != '' ? $requestedData['description'] : ''}}" placeholder="{{ __('Description') }}" maxlength="1000">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>{{ __('Username') }}
                                    </label>
                                    <input type="text" name="username" class="form-control" value="{{ isset($requestedData['username']) && $requestedData['username'] != '' ? $requestedData['username'] : ''}}" placeholder="{{ __('Username') }}" maxlength="20">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>{{ __('Created Date') }}
                                    </label>
                                    <input type="text" class="form-control" name="daterange" value="{{ isset($requestedData['daterange']) && $requestedData['daterange'] != '' ? $requestedData['daterange'] : ''}}" placeholder="{{ __('Created Date') }}" / maxlength="50">
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
                        <button type="button" id="reset" class="btn btn-primary" onclick="window.location='{{ route("meetings") }}'">{{ __('Reset') }}</button>
                    </form>
                </div>
        </div>