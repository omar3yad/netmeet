@extends('layouts.admin')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('content')
    <div class="card">
        <div class="card-body">
            @include('include.message')
            <div class="row">
                <div class="col-md-6">
                    <a href="{{ route('activity-log.export', request()->query()) }}"<button class="btn btn-primary btn-sm">
                        <i class="fas fa-cloud-download-alt"></i> {{ 'Export' }}
                        </button></a>
                </div>
                <div class="col-md-12 search-section">
                    <a class="search-penal collapsed" data-toggle="collapse" href="#collapseExample" role="button"
                        aria-expanded="false" aria-controls="collapseExample">
                        {{ __('Search') }}
                        <i class="fas fa-angle-right ml-auto"></i>
                    </a>
                </div>
            </div>
            @include('admin.activity-log.search')
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('ID') }}</th>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Event Type') }}</th>
                            <th>{{ __('Log') }}</th>
                            <th>{{ __('IP') }}</th>
                            <th>{{ __('Created At') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $key => $value)
                            <tr>
                                <td>{{ $value->id }}</td>
                                <td>{{ $value->whom }}</td>
                                <td>{{ $value->event_type }}</td>
                                <td>{{ $value->log }}</td>
                                <td>{{ $value->ip }}</td>
                                <td>{{ $value->created_at }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>{{ __('ID') }}</th>
                            <!-- <th>{{ __('Primary ID') }}</th> -->
                            <th>{{ __('User') }}</th>
                            <!-- <th>{{ __('Model') }}</th> -->
                            <th>{{ __('Event Type') }}</th>
                            <th>{{ __('Log') }}</th>
                            <th>{{ __('IP') }}</th>
                            <th>{{ __('Created At') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="float-right">
            {{ $logs->links() }}
        </div>
    </div>
    </div>
@endsection
