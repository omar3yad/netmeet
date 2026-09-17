<div class="collapse search-body {{ Route::is('pages.search') ? 'show' : '' }}" id="collapseExample">
    <div class="card card-body">
        <form id="search" action="{{ route('pages.search') }}" enctype="multipart/form-data" method="get">
            @csrf
            <input type="hidden" name="page" value="pages">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ __('Title') }}
                        </label>
                        <input type="text" name="title" class="form-control" value="{{ isset($requestedData['title']) && $requestedData['title'] != '' ? $requestedData['title'] : ''}}" placeholder="{{ __('Title') }}" maxlength="255">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ __('Slug') }}
                        </label>
                        <input type="text" name="slug" class="form-control" value="{{ isset($requestedData['slug']) && $requestedData['slug'] != '' ? $requestedData['slug'] : ''}}" placeholder="{{ __('Slug') }}" maxlength="255">
                    </div>
                </div>
            </div>
            <button type="submit" name="submit" id="searchSubmit" class="btn btn-primary" disabled>{{ __('Search') }}</button>
            <button type="button" id="reset" class="btn btn-primary" onclick="window.location='{{ route("pages") }}'">{{ __('Reset') }}</button>
        </form>
    </div>
</div>