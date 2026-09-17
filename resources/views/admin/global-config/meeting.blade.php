@extends('admin.global-config.index')

@section('global-config-content')
    <form action="{{ route('meeting.update') }}" method="post">
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
                        value="{{ old('STUN_URL') ?? getSetting('STUN_URL') }}" placeholder="{{ __('STUN URL') }}">
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
                        <i class="fa fa-info-circle info"
                            title="{{ __('TURN URL for WebRTC. Add your server\'s TURN URL once you finish installing it.') }}"></i>
                    </label>
                    <input type="text" name="TURN_URL"
                        class="form-control{{ $errors->has('TURN_URL') ? ' is-invalid' : '' }}"
                        value="{{ old('TURN_URL') ?? getSetting('TURN_URL') }}" placeholder="{{ __('TURN URL') }}">
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
                        <i class="fa fa-info-circle info"
                            title="{{ __('Enter TURN username (NOT server\'s username).') }}"></i>
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
                        <i class="fa fa-info-circle info"
                            title="{{ __('Enter TURN password (NOT server\'s passsword).') }}"></i>
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

        <hr>

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label>{{ __('Default Username') }}
                        <i class="fa fa-info-circle info"
                            title="{{ __('This will be the default username when a guest user joins the meeting without entering his name.') }}"></i>
                    </label>
                    <input type="text" name="DEFAULT_USERNAME"
                        class="form-control{{ $errors->has('DEFAULT_USERNAME') ? ' is-invalid' : '' }}"
                        value="{{ old('DEFAULT_USERNAME') ?? getSetting('DEFAULT_USERNAME') }}"
                        placeholder="{{ __('Default Username') }}">
                    @if ($errors->has('DEFAULT_USERNAME'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('DEFAULT_USERNAME') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label>{{ __('Moderator Rights') }}
                        <i class="fa fa-info-circle info"
                            title="{{ __('If on, the moderator will be able to accept/reject requests to join the room and can kick the users out of the room.') }}"></i>
                    </label>
                    <select name="MODERATOR_RIGHTS"
                        class="form-control{{ $errors->has('MODERATOR_RIGHTS') ? ' is-invalid' : '' }}">
                        <option value="enabled" @if (old('MODERATOR_RIGHTS') ? old('MODERATOR_RIGHTS') == 'enabled' : getSetting('MODERATOR_RIGHTS') == 'enabled') selected @endif>{{ __('On') }}
                        </option>
                        <option value="disabled" @if (old('MODERATOR_RIGHTS') ? old('MODERATOR_RIGHTS') == 'disabled' : getSetting('MODERATOR_RIGHTS') == 'disabled') selected @endif>{{ __('Off') }}
                        </option>
                    </select>
                    @if ($errors->has('MODERATOR_RIGHTS'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('MODERATOR_RIGHTS') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label>{{ __('End URL') }}
                        <i class="fa fa-info-circle info"
                            title="{{ __('A web page to display when the meeting is over. Enter a URL. Leave \'null\' to reload the page. Set custom page like this: /pages/thank-you') }}"></i>
                    </label>
                    <input type="text" name="END_URL"
                        class="form-control{{ $errors->has('END_URL') ? ' is-invalid' : '' }}"
                        value="{{ old('END_URL') ?? getSetting('END_URL') }}" placeholder="{{ __('End URL') }}">
                    @if ($errors->has('END_URL'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('END_URL') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label>{{ __('Limited Screen Sharing') }}
                        <i class="fa fa-info-circle info"
                            title="{{ __('If on, the meeting will allow only one screen share at a time') }}"></i>
                    </label>
                    <select name="LIMITED_SCREEN_SHARE"
                        class="form-control{{ $errors->has('LIMITED_SCREEN_SHARE') ? ' is-invalid' : '' }}">
                        <option value="enabled" @if (old('LIMITED_SCREEN_SHARE')
                                ? old('LIMITED_SCREEN_SHARE') == 'enabled'
                                : getSetting('LIMITED_SCREEN_SHARE') == 'enabled') selected @endif>{{ __('On') }}
                        </option>
                        <option value="disabled" @if (old('LIMITED_SCREEN_SHARE')
                                ? old('LIMITED_SCREEN_SHARE') == 'disabled'
                                : getSetting('LIMITED_SCREEN_SHARE') == 'disabled') selected @endif>{{ __('Off') }}
                        </option>
                    </select>
                    @if ($errors->has('LIMITED_SCREEN_SHARE'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('LIMITED_SCREEN_SHARE') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
    </form>
@endsection
