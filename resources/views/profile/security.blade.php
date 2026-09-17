@extends('profile.index')

@section('profile-content')
    @include('include.message')

    <form action="{{ route('profile.security') }}" method="post">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="i-current-password">{{ __('Current password') }}</label>
                            <input type="password" name="current_password" id="i-current-password"
                                class="form-control{{ $errors->has('current_password') ? ' is-invalid' : '' }}"
                                placeholder="{{ __('Current password') }}">
                            @if ($errors->has('current_password'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('current_password') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="i-password">{{ __('New password') }}</label>
                            <input type="password" name="password" id="i-password"
                                class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                placeholder="{{ __('New password') }}">
                            @if ($errors->has('password'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="i-password-confirmation">{{ __('Confirm new password') }}</label>
                            <input type="password" name="password_confirmation" id="i-password-confirmation"
                                class="form-control{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}"
                                placeholder="{{ __('Confirm new password') }}">
                            @if ($errors->has('password_confirmation'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('password_confirmation') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>    
                <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
            </div>    
        </div>
    </form>
@endsection
