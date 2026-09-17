@extends('profile.index')

@section('profile-content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="api_token">{{ __('API Token') }}</label>
                    <input type="text" id="api_token" class="form-control" value="{{ isDemoMode() ? __('This field is hidden in the demo mode') : $api_token }}" disabled>
                </div>
            </div>
        </div>
        <button type="button" id="copyApiToken" class="btn btn-primary">{{ __('Copy') }}</button>
    </div>
</div>
@endsection
