@extends('admin.global-config.index')

@section('global-config-content')
    <form action="{{ route('networking.update') }}" method="post">
        @csrf
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label>{{ __('Signaling URL') }}
                        <i class="fa fa-info-circle info" title="{{ __('Signaling server (NodeJS) URL.') }}"></i>
                    </label>
                    <input type="text" name="SIGNALING_URL"
                        class="form-control{{ $errors->has('SIGNALING_URL') ? ' is-invalid' : '' }}"
                        value="{{ old('SIGNALING_URL') ?? getSetting('SIGNALING_URL') }}"
                        placeholder="{{ __('Signaling URL') }}">
                    @if ($errors->has('SIGNALING_URL'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('SIGNALING_URL') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label>{{ __('STUN URL') }}
                        <i class="fa fa-info-circle info" title="{{ __('STUN URL for WebRTC. No need to update.') }}"></i>
                    </label>
                    <input type="text" name="STUN_URL"
                        class="form-control{{ $errors->has('STUN_URL') ? ' is-invalid' : '' }}"
                        value="{{ old('STUN_URL') ?? getSetting('STUN_URL') }}"
                        placeholder="{{ __('STUN URL') }}">
                    @if ($errors->has('STUN_URL'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('STUN_URL') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label>{{ __('TURN URL') }}
                        <i class="fa fa-info-circle info" title="{{ __('TURN URL for WebRTC. Add your server\'s TURN URL once you finish installing it.') }}"></i>
                    </label>
                    <input type="text" name="TURN_URL"
                        class="form-control{{ $errors->has('TURN_URL') ? ' is-invalid' : '' }}"
                        value="{{ old('TURN_URL') ?? getSetting('TURN_URL') }}"
                        placeholder="{{ __('TURN URL') }}">
                    @if ($errors->has('TURN_URL'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('TURN_URL') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label>{{ __('TURN Username') }}
                        <i class="fa fa-info-circle info" title="{{ __('Enter TURN username (NOT server\'s username).') }}"></i>
                    </label>
                    <input type="text" name="TURN_USERNAME"
                        class="form-control{{ $errors->has('TURN_USERNAME') ? ' is-invalid' : '' }}"
                        value="{{ old('TURN_USERNAME') ?? getSetting('TURN_USERNAME') }}"
                        placeholder="{{ __('TURN Username') }}">
                    @if ($errors->has('TURN_USERNAME'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('TURN_USERNAME') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label>{{ __('TURN Password') }}
                        <i class="fa fa-info-circle info" title="{{ __('Enter TURN password (NOT server\'s passsword).') }}"></i>
                    </label>
                    <input type="password" name="TURN_PASSWORD"
                        class="form-control{{ $errors->has('TURN_PASSWORD') ? ' is-invalid' : '' }}"
                        value="{{ old('TURN_PASSWORD') ?? getSetting('TURN_PASSWORD') }}"
                        placeholder="{{ __('TURN Password') }}">
                    @if ($errors->has('TURN_PASSWORD'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('TURN_PASSWORD') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
    </form>
@endsection
