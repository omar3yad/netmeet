@extends('admin.global-config.index')

@section('global-config-content')
<form action="{{ route('company.update') }}" enctype="multipart/form-data" method="post">
    @csrf
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('Company Name') }}
                    <i class="fa fa-info-circle info" title="{{ __('Company Name will be visible in the entire application.') }}"></i>
                </label>
                <input type="text" name="COMPANY_NAME" class="form-control{{ $errors->has('COMPANY_NAME') ? ' is-invalid' : '' }}" value="{{ old('COMPANY_NAME') ?? getSetting('COMPANY_NAME') }}" placeholder="{{ __('Company Name') }}">
                @if ($errors->has('COMPANY_NAME'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('COMPANY_NAME') }}</strong>
                </span>
                @endif
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('Address') }}
                    <i class="fa fa-info-circle info" title="{{ __('Company Address will be visible on invoice.') }}"></i>
                </label>
                <input type="text" name="COMPANY_ADDRESS" class="form-control{{ $errors->has('COMPANY_ADDRESS') ? ' is-invalid' : '' }}" value="{{ old('COMPANY_ADDRESS') ?? getSetting('COMPANY_ADDRESS') }}" placeholder="{{ __('Company Address') }}">
                @if ($errors->has('COMPANY_ADDRESS'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('COMPANY_ADDRESS') }}</strong>
                </span>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('City') }}
                    <i class="fa fa-info-circle info" title="{{ __('Company City will be visible on invoice.') }}"></i>
                </label>
                <input type="text" name="COMPANY_CITY"  class="form-control{{ $errors->has('COMPANY_CITY') ? ' is-invalid' : '' }}" value="{{ old('COMPANY_CITY') ?? getSetting('COMPANY_CITY') }}" placeholder="{{ __('City') }}">
                @if ($errors->has('COMPANY_CITY'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('COMPANY_CITY') }}</strong>
                </span>
                @endif
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('State') }}
                    <i class="fa fa-info-circle info" title="{{ __('Company State will be visible on invoice.') }}"></i>
                </label>
                <input type="text" name="COMPANY_STATE"  class="form-control{{ $errors->has('COMPANY_STATE') ? ' is-invalid' : '' }}" value="{{ old('COMPANY_STATE') ?? getSetting('COMPANY_STATE') }}" placeholder="{{ __('State') }}">
                @if ($errors->has('COMPANY_STATE'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('COMPANY_STATE') }}</strong>
                </span>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('Postal Code') }}
                    <i class="fa fa-info-circle info" title="{{ __('Company Postal code will be visible on invoice.') }}"></i>
                </label>
                <input type="text" name="COMPANY_POSTAL_CODE" class="form-control{{ $errors->has('COMPANY_POSTAL_CODE') ? ' is-invalid' : '' }}" value="{{ old('COMPANY_POSTAL_CODE') ?? getSetting('COMPANY_POSTAL_CODE') }}" placeholder="{{ __('Postal Code') }}">
                @if ($errors->has('COMPANY_POSTAL_CODE'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('COMPANY_POSTAL_CODE') }}</strong>
                </span>
                @endif
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="i-country">{{ __('Country') }}</label>
                <select name="COMPANY_COUNTRY" id="i-country" class="custom-select{{ $errors->has('COMPANY_COUNTRY') ? ' is-invalid' : '' }}">
                    @foreach (config('countries') as $key => $value)
                    <option value="{{ $key }}" test="{{old('COMPANY_COUNTRY')}}" @if (getSetting('COMPANY_COUNTRY') == $key) selected @endif>
                        {{ __($value) }}
                    </option>
                    @endforeach
                </select>
                @if ($errors->has('COMPANY_COUNTRY'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('COMPANY_COUNTRY') }}</strong>
                </span>
                @endif
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('Phone') }}
                    <i class="fa fa-info-circle info" title="{{ __('Company Phone Number will be visible on invoice.') }}"></i>
                </label>
                <input type="text" name="COMPANY_PHONE" class="form-control{{ $errors->has('COMPANY_PHONE') ? ' is-invalid' : '' }}" value="{{ old('COMPANY_PHONE') ?? getSetting('COMPANY_PHONE') }}" placeholder="{{ __('Phone') }}">
                @if ($errors->has('COMPANY_PHONE'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('COMPANY_PHONE') }}</strong>
                </span>
                @endif
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('Email') }}
                    <i class="fa fa-info-circle info" title="{{ __('Company Email will be visible on invoice.') }}"></i>
                </label>
                <input type="text" name="COMPANY_EMAIL" class="form-control{{ $errors->has('COMPANY_EMAIL') ? ' is-invalid' : '' }}" value="{{ old('COMPANY_EMAIL') ?? getSetting('COMPANY_EMAIL') }}" placeholder="{{ __('Company Email') }}">
                @if ($errors->has('COMPANY_EMAIL'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('COMPANY_EMAIL') }}</strong>
                </span>
                @endif
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>{{ __('Tax ID') }}
                    <i class="fa fa-info-circle info" title="{{ __('Company Tax(HST/GST/VAT) ID will be visible on invoice.') }}"></i>
                </label>
                <input type="text" name="COMPANY_TAX_ID" class="form-control{{ $errors->has('COMPANY_TAX_ID') ? ' is-invalid' : '' }}" value="{{ old('COMPANY_TAX_ID') ?? getSetting('COMPANY_TAX_ID') }}" placeholder="{{ __('Tax ID(HST/GST/VAT)') }}">
                @if ($errors->has('COMPANY_TAX_ID'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('COMPANY_TAX_ID') }}</strong>
                </span>
                @endif
            </div>
        </div>
    </div>
    <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
</form>
@endsection