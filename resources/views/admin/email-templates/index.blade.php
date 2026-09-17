@extends('layouts.admin')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)

@section('content')
    <div class="card">
        <div class="card-body">
            @include('include.message')
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Slug') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pages as $key => $value)
                        <tr>
                            <td>{{ $value->id }}</td>
                            <td>{{ $value->name }}</td>
                            <td>{{ $value->slug }}</td>
                            <td>
                                <a href="/admin/email-templates/edit/{{ $value->id }}">
                                    <button class="btn btn-primary btn-sm" title="{{ __('Edit') }}">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Slug') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="card-footer">
            <div class="float-right">
                {{ $pages->links() }}
            </div>
        </div>
    </div>
@endsection
