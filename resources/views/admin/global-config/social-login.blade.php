@extends('admin.global-config.index')

@section('global-config-content')
<form action="{{ route('sociallogin.update') }}" method="post">
    @csrf

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('Google') }}
                    <i class="fa fa-info-circle info" title="{{ __('This will enable login with google link on login page.') }}"></i>
                </label>
                <select name="GOOGLE_SOCIAL_LOGIN" class="form-control{{ $errors->has('GOOGLE_SOCIAL_LOGIN') ? ' is-invalid' : '' }}">
                    <option value="enabled" @if (old('GOOGLE_SOCIAL_LOGIN') ? old('GOOGLE_SOCIAL_LOGIN')=='enabled' : getSetting('GOOGLE_SOCIAL_LOGIN')=='enabled' ) selected @endif>{{ __('On') }}
                    </option>
                    <option value="disabled" @if (old('GOOGLE_SOCIAL_LOGIN') ? old('GOOGLE_SOCIAL_LOGIN')=='disabled' : getSetting('GOOGLE_SOCIAL_LOGIN')=='disabled' ) selected @endif>{{ __('Off') }}
                    </option>
                </select>
                @if ($errors->has('GOOGLE_SOCIAL_LOGIN'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('GOOGLE_SOCIAL_LOGIN') }}</strong>
                </span>
                @endif
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('Client ID') }}
                    <i class="fa fa-info-circle info" title="{{ __('Client ID') }}"></i>
                </label>
                <input type="text" name="GOOGLE_CLIENT_ID" class="form-control{{ $errors->has('GOOGLE_CLIENT_ID') ? ' is-invalid' : '' }}" value="{{ isDemoMode() ? __('This field is hidden in the demo mode') : old('GOOGLE_CLIENT_ID') ?? getSetting('GOOGLE_CLIENT_ID') }}" placeholder="{{ __('Client ID') }}">
                @if ($errors->has('GOOGLE_CLIENT_ID'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('GOOGLE_CLIENT_ID') }}</strong>
                </span>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('Client Secret') }}
                    <i class="fa fa-info-circle info" title="{{ __('Client Secret') }}"></i>
                </label>
                <input type="text" name="GOOGLE_CLIENT_SECRET" class="form-control{{ $errors->has('GOOGLE_CLIENT_SECRET') ? ' is-invalid' : '' }}" value="{{ isDemoMode() ? __('This field is hidden in the demo mode') : old('GOOGLE_CLIENT_SECRET') ?? getSetting('GOOGLE_CLIENT_SECRET') }}" placeholder="{{ __('Client Secret') }}">
                @if ($errors->has('GOOGLE_CLIENT_SECRET'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('GOOGLE_CLIENT_SECRET') }}</strong>
                </span>
                @endif
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="i-callback-wh-url">{{ __('Callback URL') }}</label>
                <div class="input-group">
                    <input type="text" dir="ltr" name="google_callback_url" id="i-google-cb-url" class="form-control" value="{{ route('login.google.callback') }}" readonly>
                    <div class="input-group-append">
                        <div class="btn btn-primary" id="google_cb_url_copy">{{ __('Copy') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('Facebook') }}
                    <i class="fa fa-info-circle info" title="{{ __('This will enable login with facebook link on login page.') }}"></i>
                </label>
                <select name="FACEBOOK_SOCIAL_LOGIN" class="form-control{{ $errors->has('FACEBOOK_SOCIAL_LOGIN') ? ' is-invalid' : '' }}">
                    <option value="enabled" @if (old('FACEBOOK_SOCIAL_LOGIN') ? old('FACEBOOK_SOCIAL_LOGIN')=='enabled' : getSetting('FACEBOOK_SOCIAL_LOGIN')=='enabled' ) selected @endif>{{ __('On') }}
                    </option>
                    <option value="disabled" @if (old('FACEBOOK_SOCIAL_LOGIN') ? old('FACEBOOK_SOCIAL_LOGIN')=='disabled' : getSetting('FACEBOOK_SOCIAL_LOGIN')=='disabled' ) selected @endif>{{ __('Off') }}
                    </option>
                </select>
                @if ($errors->has('FACEBOOK_SOCIAL_LOGIN'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('FACEBOOK_SOCIAL_LOGIN') }}</strong>
                </span>
                @endif
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('Client ID') }}
                    <i class="fa fa-info-circle info" title="{{ __('Client ID') }}"></i>
                </label>
                <input type="text" name="FACEBOOK_CLIENT_ID" class="form-control{{ $errors->has('FACEBOOK_CLIENT_ID') ? ' is-invalid' : '' }}" value="{{ isDemoMode() ? __('This field is hidden in the demo mode') : old('FACEBOOK_CLIENT_ID') ?? getSetting('FACEBOOK_CLIENT_ID') }}" placeholder="{{ __('Client ID') }}">
                @if ($errors->has('FACEBOOK_CLIENT_ID'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('FACEBOOK_CLIENT_ID') }}</strong>
                </span>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('Client Secret') }}
                    <i class="fa fa-info-circle info" title="{{ __('Client Secret') }}"></i>
                </label>
                <input type="text" name="FACEBOOK_CLIENT_SECRET" class="form-control{{ $errors->has('FACEBOOK_CLIENT_SECRET') ? ' is-invalid' : '' }}" value="{{ isDemoMode() ? __('This field is hidden in the demo mode') : old('FACEBOOK_CLIENT_SECRET') ?? getSetting('FACEBOOK_CLIENT_SECRET') }}" placeholder="{{ __('Client Secret') }}">
                @if ($errors->has('FACEBOOK_CLIENT_SECRET'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('FACEBOOK_CLIENT_SECRET') }}</strong>
                </span>
                @endif
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="i-callback-wh-url">{{ __('Callback URL') }}</label>
                <div class="input-group">
                    <input type="text" dir="ltr" name="facebook_callback_url" id="i-facebook-cb-url" class="form-control" value="{{ route('login.facebook.callback') }}" readonly>
                    <div class="input-group-append">
                        <div class="btn btn-primary" id="facebook_cb_url_copy">{{ __('Copy') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('Linkedin') }}
                    <i class="fa fa-info-circle info" title="{{ __('This will enable login with linkedin link on login page.') }}"></i>
                </label>
                <select name="LINKEDIN_SOCIAL_LOGIN" class="form-control{{ $errors->has('LINKEDIN_SOCIAL_LOGIN') ? ' is-invalid' : '' }}">
                    <option value="enabled" @if (old('LINKEDIN_SOCIAL_LOGIN') ? old('LINKEDIN_SOCIAL_LOGIN')=='enabled' : getSetting('LINKEDIN_SOCIAL_LOGIN')=='enabled' ) selected @endif>{{ __('On') }}
                    </option>
                    <option value="disabled" @if (old('LINKEDIN_SOCIAL_LOGIN') ? old('LINKEDIN_SOCIAL_LOGIN')=='disabled' : getSetting('LINKEDIN_SOCIAL_LOGIN')=='disabled' ) selected @endif>{{ __('Off') }}
                    </option>
                </select>
                @if ($errors->has('LINKEDIN_SOCIAL_LOGIN'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('LINKEDIN_SOCIAL_LOGIN') }}</strong>
                </span>
                @endif
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('Client ID') }}
                    <i class="fa fa-info-circle info" title="{{ __('Client ID') }}"></i>
                </label>
                <input type="text" name="LINKEDIN_CLIENT_ID" class="form-control{{ $errors->has('LINKEDIN_CLIENT_ID') ? ' is-invalid' : '' }}" value="{{ isDemoMode() ? __('This field is hidden in the demo mode') : old('LINKEDIN_CLIENT_ID') ?? getSetting('LINKEDIN_CLIENT_ID') }}" placeholder="{{ __('Client ID') }}">
                @if ($errors->has('LINKEDIN_CLIENT_ID'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('LINKEDIN_CLIENT_ID') }}</strong>
                </span>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('Client Secret') }}
                    <i class="fa fa-info-circle info" title="{{ __('Client Secret') }}"></i>
                </label>
                <input type="text" name="LINKEDIN_CLIENT_SECRET" class="form-control{{ $errors->has('LINKEDIN_CLIENT_SECRET') ? ' is-invalid' : '' }}" value="{{ isDemoMode() ? __('This field is hidden in the demo mode') : old('LINKEDIN_CLIENT_SECRET') ?? getSetting('LINKEDIN_CLIENT_SECRET') }}" placeholder="{{ __('Client Secret') }}">
                @if ($errors->has('LINKEDIN_CLIENT_SECRET'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('LINKEDIN_CLIENT_SECRET') }}</strong>
                </span>
                @endif
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="i-callback-wh-url">{{ __('Callback URL') }}</label>
                <div class="input-group">
                    <input type="text" dir="ltr" name="linkedin_callback_url" id="i-linkedin-cb-url" class="form-control" value="{{ route('login.linkedin.callback') }}" readonly>
                    <div class="input-group-append">
                        <div class="btn btn-primary" id="linkedin_cb_url_copy">{{ __('Copy') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('Twitter') }}
                    <i class="fa fa-info-circle info" title="{{ __('This will enable login with twitter link on login page.') }}"></i>
                </label>
                <select name="TWITTER_SOCIAL_LOGIN" class="form-control{{ $errors->has('TWITTER_SOCIAL_LOGIN') ? ' is-invalid' : '' }}">
                    <option value="enabled" @if (old('TWITTER_SOCIAL_LOGIN') ? old('TWITTER_SOCIAL_LOGIN')=='enabled' : getSetting('TWITTER_SOCIAL_LOGIN')=='enabled' ) selected @endif>{{ __('On') }}
                    </option>
                    <option value="disabled" @if (old('TWITTER_SOCIAL_LOGIN') ? old('TWITTER_SOCIAL_LOGIN')=='disabled' : getSetting('TWITTER_SOCIAL_LOGIN')=='disabled' ) selected @endif>{{ __('Off') }}
                    </option>
                </select>
                @if ($errors->has('TWITTER_SOCIAL_LOGIN'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('TWITTER_SOCIAL_LOGIN') }}</strong>
                </span>
                @endif
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('Client ID') }}
                    <i class="fa fa-info-circle info" title="{{ __('Client ID') }}"></i>
                </label>
                <input type="text" name="TWITTER_CLIENT_ID" class="form-control{{ $errors->has('TWITTER_CLIENT_ID') ? ' is-invalid' : '' }}" value="{{ isDemoMode() ? __('This field is hidden in the demo mode') : old('TWITTER_CLIENT_ID') ?? getSetting('TWITTER_CLIENT_ID') }}" placeholder="{{ __('Client ID') }}">
                @if ($errors->has('TWITTER_CLIENT_ID'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('TWITTER_CLIENT_ID') }}</strong>
                </span>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('Client Secret') }}
                    <i class="fa fa-info-circle info" title="{{ __('Client Secret') }}"></i>
                </label>
                <input type="text" name="TWITTER_CLIENT_SECRET" class="form-control{{ $errors->has('TWITTER_CLIENT_SECRET') ? ' is-invalid' : '' }}" value="{{ isDemoMode() ? __('This field is hidden in the demo mode') : old('TWITTER_CLIENT_SECRET') ?? getSetting('TWITTER_CLIENT_SECRET') }}" placeholder="{{ __('Client Secret') }}">
                @if ($errors->has('TWITTER_CLIENT_SECRET'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('TWITTER_CLIENT_SECRET') }}</strong>
                </span>
                @endif
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="i-callback-wh-url">{{ __('Callback URL') }}</label>
                <div class="input-group">
                    <input type="text" dir="ltr" name="twitter_callback_url" id="i-twitter-cb-url" class="form-control" value="{{ route('login.twitter.callback') }}" readonly>
                    <div class="input-group-append">
                        <div class="btn btn-primary" id="twitter_cb_url_copy">{{ __('Copy') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
</form>
@endsection