@extends('layouts.app')

@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)
@section('content')


<section class="auth-section">
    <div class="container auth-container">
        <div class="row align-items-center h-100">
            <div class="col-md-1"></div>
            <div class="col-12 col-md-10 main-authsection h-100">
                <div class="row h-100 table-display">
                    <div class="col-12 col-md-6 h-100 auth-text-section">
                        <div class="text-center">
                            <h3 class="white-text">{{ __('Create Your Username') }}</h3>
                        </div>
                        <div class="bg-set"></div>
                    </div>
                    <div class="col-12 col-md-6 auth-enterdata h-100">
                        <div class="card auth-info mb-0">
                            <div class="card-header text-center">
                                {{ __('Create your Username') }}
                            </div>
                            <div class="card-body">

                                <form method="POST" action="{{ route('username.add.verify') }}">
                                    @csrf
                                    <div class="form-group row">
                                        <div class="col-12">
                                        <input id="username" type="username"
                                            class="form-control @error('username') is-invalid @enderror bg-transparent"
                                            name="username" value="{{ old('username', $username ?? '') }}" required
                                            autocomplete="username" placeholder="Username" autofocus>
                                        @error('username')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                        </div>
                                    </div>


                                    <div class="form-group row mb-0">
                                        <div class="col-12 text-center">
                                            <button id="loginButton" type="submit" class="btn btn-primary w-100 mb-2">
                                                {{ __('Save') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection