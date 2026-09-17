@extends('layouts.admin')

@section('page', $page)
@section('title', getSetting('APPLICATION_NAME') . ' | ' . $page)
@section('style')

@endsection
@section('content')
    <div class="card">
        <div class="card-body">
            @include('include.message')

            <form action="{{ route('updateEmailTemplate', $data->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>{{ __('Name') }}</label>
                            <input type="text" name="name" placeholder="{{ __('Name') }}"
                                class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                                value="{{ $data->name }}" maxlength="64" autofocus>
                            @if ($errors->has('name'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('name') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>{{ __('Slug') }}</label>
                            <input type="text" name="slug" placeholder="{{ __('Slug') }}"
                                class="form-control{{ $errors->has('slug') ? ' is-invalid' : '' }}"
                                value="{{ $data->slug }}" maxlength="255" readonly disabled>
                            @if ($errors->has('slug'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('slug') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label>{{ __('Content') }}</label>
                            <p>
                                @if ($data->slug == 'user-creation' || $data->slug == 'meeting-invitation')
                                    <button type="button" class="btn btn-warning btn-sm add-variable">USER_NAME</button>
                                @endif
                                @if ($data->slug == 'user-creation')
                                    <button type="button" class="btn btn-warning btn-sm add-variable">USER_EMAIL</button>
                                @endif
                                @if ($data->slug == 'user-creation')
                                    <button type="button" class="btn btn-warning btn-sm add-variable">USER_PASSWORD</button>
                                @endif
                                @if ($data->slug == 'meeting-invitation')
                                    <button type="button" class="btn btn-warning btn-sm add-variable">MEETING_ID</button>
                                @endif
                                @if ($data->slug == 'meeting-invitation')
                                    <button type="button" class="btn btn-warning btn-sm add-variable">MEETING_PASSWORD</button>
                                @endif
                                @if ($data->slug == 'meeting-invitation')
                                    <button type="button" class="btn btn-warning btn-sm add-variable">MEETING_TITLE</button>
                                @endif
                                @if ($data->slug == 'meeting-invitation')
                                    <button type="button" class="btn btn-warning btn-sm add-variable">MEETING_DESCRIPTION</button>
                                @endif
                                @if ($data->slug == 'meeting-invitation')
                                    <button type="button" class="btn btn-warning btn-sm add-variable">MEETING_DATE</button>
                                @endif
                                @if ($data->slug == 'meeting-invitation')
                                    <button type="button" class="btn btn-warning btn-sm add-variable">MEETING_TIME</button>
                                @endif
                                @if ($data->slug == 'meeting-invitation')
                                    <button type="button" class="btn btn-warning btn-sm add-variable">MEETING_TIMEZONE</button>
                                @endif
                                @if ($data->slug == 'two-factor-auth-code')
                                    <button type="button" class="btn btn-warning btn-sm add-variable">CODE</button>
                                @endif
                            </p>

                            <textarea id="content" rows="6" name="content"
                                class="form-control {{ $errors->has('content') ? ' is-invalid' : '' }}">{{ $data->content }}</textarea>
                            @if ($errors->has('content'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('content') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <button type="submit" name="save" class="btn btn-primary">{{ __('Save') }}</button>
                <a href="{{ route('admin.emailTemplates') }}"><button type="button"
                        class="btn btn-default">{{ __('Back') }}</button></a>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('js/ckeditor/ckeditor.js') }}"></script>

    <script>
        $(function() {
            CKEDITOR.replace('content', {
                toolbarGroups: [{
                        "name": "basicstyles",
                        "groups": ["basicstyles", "links"]
                    },
                    {
                        "name": 'paragraph',
                        "groups": ['list']
                    },
                    {
                        "name": "styles",
                        "groups": ["styles", 'Font', 'FontSize']
                    },
                ],
                removeButtons: 'Styles,Superscript,Subscript,Strike,Anchor',
                language: '{{ getSelectedLanguage()->code }}',

            });

            $('.add-variable').on('click', function() {
                let value = $(this).text();
                value = value.trim();
                CKEDITOR.instances['content'].insertText('[' + value + ']');
            });
        });
    </script>
@endsection
