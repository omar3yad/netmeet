@component('mail::layout')
{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

{{-- Body --}}
@php
$body = str_replace('[USER_NAME]',$meeting['user']['username'],$body);
$body = str_replace('[MEETING_ID]',$meeting['meeting_id'],$body);
$body = str_replace('[MEETING_TITLE]',$meeting['title'],$body);
$body = str_replace('[MEETING_PASSWORD]',$meeting['password'],$body);
$body = str_replace('[MEETING_DATE]',$meeting['date'],$body);
$body = str_replace('[MEETING_TIME]',$meeting['time'],$body);
$body = str_replace('[MEETING_TIMEZONE]',$meeting['timezone'],$body);
$body = str_replace('[MEETING_DESCRIPTION]',$meeting['description'],$body);
@endphp
{!! @$body !!}
@component('mail::button', ['url' => Request::root() . '/meeting/' . $meeting['meeting_id']])
{{ __('Join Now') }}
@endcomponent

{{-- Subcopy --}}
@isset($subcopy)
@slot('subcopy')
@component('mail::subcopy')
{{ $subcopy }}
@endcomponent
@endslot
@endisset

{{-- Footer --}}
@slot('footer')
@component('mail::footer')
© {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
@endcomponent
@endslot
@endcomponent
