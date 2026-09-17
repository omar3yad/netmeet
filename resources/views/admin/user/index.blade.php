@extends('layouts.admin')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('content')
    <div class="card">
        <div class="card-body">
            @include('include.message')
            <div class="row">
                <div class="col-md-6">
                    <a href="{{ route('createUser') }}">
                        <button class="btn btn-primary btn-sm" id="createUser" title="{{ __('Create User') }}">
                            {{ __('Create') }}
                        </button>
                    </a>
                    <a href="{{ route('user.export', request()->query()) }}">
                        <button class="btn btn-primary btn-sm">
                            <i class="fas fa-cloud-download-alt"></i> {{ 'Export' }}
                        </button>
                    </a>
                </div>
                <div class="col-md-12 search-section">
                    <a class="search-penal collapsed" data-toggle="collapse" href="#collapseExample" role="button"
                        aria-expanded="false" aria-controls="collapseExample">
                        {{ __('Search') }}
                        <i class="fas fa-angle-right ml-auto"></i>
                    </a>
                </div>
            </div>
            @include('admin.user.search')
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Username') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Plan') }}</th>
                        <th>{{ __('Source') }}</th>
                        <th>{{ __('Created Date') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $key => $value)
                        <tr>
                            <td>{{ $value->id }}</td>
                            <td>{{ $value->username }}</td>
                            <td>{{ $value->email }}</td>
                            <!-- <td>{{ $value->name }}</td> -->
                            <td>
                                <select class="form-control assignPlan">
                                    @foreach ($plans as $key => $val)
                                        <option value="{{ $val }}|{{ $value->id }}"
                                            {{ $val == $value->plan_id ? 'selected' : '' }}>{{ $key }}</option>
                                    @endforeach
                                </select>
                            </td>
                            @if ($value->facebook_id)
                                <td>Facebook</td>
                            @elseif ($value->twitter_id)
                                <td>Twitter</td>
                            @elseif ($value->google_id)
                                <td>Google</td>
                            @elseif ($value->linkedin_id)
                                <td>LinkedIn</td>
                            @else
                                <td>{{ __('Register') }}</td>
                            @endif

                            <td>{{ $value->created_at }}</td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input user-status"
                                        data-id="{{ $value->id }}" id="customSwitch{{ $value->id }}"
                                        {{ $value->status == 'active' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="customSwitch{{ $value->id }}"></label>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-danger btn-sm delete-user" data-id="{{ $value->id }}"
                                    title="{{ __('Delete') }}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Username') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Plan') }}</th>
                        <th>{{ __('Source') }}</th>
                        <th>{{ __('Created Date') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            {{ $users->links() }}
        </div>
    </div>
    </div>
@endsection
