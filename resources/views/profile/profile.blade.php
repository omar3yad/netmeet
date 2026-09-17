@extends('profile.index')

@section('profile-content')
    <link href="{{ asset('css/cropper.min.css') }}" rel="stylesheet">
    <script src="{{ asset('js/cropper.min.js') }}"></script>

    @include('include.message')
    <form action="{{ route('profile.profile.update') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <div class="user-profile-upload">
                                <input type="hidden" name="userid" id="userid" value="{{ getAuthUserInfo('id') }}" />
                                <div class="user-initial" for="avatar">
                                    @if (getAuthUserInfo('avatar') && file_exists(public_path('storage/avatars/' . getAuthUserInfo('avatar'))))
                                        <button class="btn btn-danger" type="button" id="removeAvatar"><i
                                                class="fa fa-times"></i></button>
                                    @endif
                                    @if (getAuthUserInfo('avatar') && file_exists(public_path('storage/avatars/' . getAuthUserInfo('avatar'))))
                                        @php $avatar = getAuthUserInfo('avatar'); @endphp
                                        <img id="imagePreview" src="{{ asset('storage/avatars/' . $avatar) }}"
                                            alt="User profile picture">
                                    @else
                                        <p id="initial">{{ ucfirst(getAuthUserInfo('username')[0]) }}
                                        </p>
                                        <img class="" style="display:none;" id="imagePreview">
                                    @endif
                                </div>

                                <input id="avatarchange" type="file"
                                    class="form-control{{ $errors->has('avatar') ? ' is-invalid' : '' }}" name="avatar"
                                    value="{{ old('avatar') }}" autocomplete="avatar" accept=".png, .jpg">
                            </div>

                            @if ($errors->has('avatar'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('avatar') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-6">
                        <div class="form-group">
                            <label for="i-name">{{ __('Username') }}</label>
                            <input type="text" name="username" id="i-name"
                                class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}"
                                value="{{ old('username') ?? $user->username }}" placeholder="{{ __('Username') }}">
                            @if ($errors->has('username'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('username') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-6">
                        <div class="form-group">
                            <label for="i-email">{{ __('Email') }}</label>
                            <input type="text" name="email" id="i-email"
                                class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                value="{{ old('email') ?? $user->email }}" placeholder="{{ __('Email') }}">
                            @if ($errors->has('email'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header" id="create-modal-add-services_header">
                    <!--begin::Modal title-->
                    <h2 class="fw-bold js-edit-title">{{ __('Crop') }}</h2>
                    <!--end::Modal title-->
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-dismiss="modal">
                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
                        <span class="svg-icon svg-icon-1">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                    transform="rotate(-45 6 17.3137)" fill="currentColor" />
                                <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                    transform="rotate(45 7.41422 6)" fill="currentColor" />
                            </svg>
                        </span>
                        <!--end::Svg Icon-->
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->
                <div class="modal-body py-10 px-lg-17 text-center">
                    <div class="crop-img-section">
                        <img id="previewImage" />
                    </div>
                </div>
                <div class="modal-footer flex-center">
                    <button type="button" class="btn btn-light me-3" data-dismiss="modal">{{ __('Close') }}</button>
                    <button id="crop_button" type="button" class="btn btn-primary">{{ __('Crop & Save') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection
