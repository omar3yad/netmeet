@extends('layouts.admin')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <a href="{{ route('meetings.export',request()->query()) }}">
                    <button class="btn btn-primary btn-sm">
                        <i class="fas fa-cloud-download-alt"></i> {{'Export' }}
                    </button>
                </a>
            </div>
            <div class="col-md-12 search-section">
                <a class="search-penal collapsed" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                    {{ __('Search') }}
                    <i class="fas fa-angle-right ml-auto"></i>
                </a>
            </div>
        </div>
        @include('admin.meeting.search')
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Meeting ID') }}</th>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Username') }}</th>
                    <th>{{ __('Password') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Time') }}</th>
                    <th>{{ __('Timezone') }}</th>
                    <th>{{ __('Created Date') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($meetings as $key => $value)
                <tr>
                    <td>{{ $value->id }}</td>
                    <td>{{ $value->meeting_id }}</td>
                    <td>{{ $value->title }}</td>
                    <td>{{ $value->description ? $value->description : '-' }}</td>
                    <td>{{ $value->username }}</td>
                    <td>{{ $value->password ? $value->password : '-' }}</td>
                    <td>{{ $value->date ? $value->date : '-' }}</td>
                    <td>{{ $value->time ? $value->time : '-' }}</td>
                    <td>{{ $value->timezone ? $value->timezone : '-' }}</td>
                    <td>{{ $value->created_at }}</td>
                    <td>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input meeting-status" data-id="{{ $value->id }}" id="customSwitch{{ $value->id }}" {{ $value->status == 'active' ? 'checked' : '' }}>
                            <label class="custom-control-label" for="customSwitch{{ $value->id }}"></label>
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-danger btn-sm delete-meeting-admin" data-id="{{ $value->id }}" title="{{ __('Delete') }}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Meeting ID') }}</th>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Username') }}</th>
                    <th>{{ __('Password') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Time') }}</th>
                    <th>{{ __('Timezone') }}</th>
                    <th>{{ __('Created Date') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Action') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="card-footer">
        <div class="float-right">
            {{ $meetings->links() }}
        </div>
    </div>
</div>
@endsection