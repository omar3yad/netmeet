@component('mail::layout')
{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

{{-- Body --}}
@php
$body = str_replace('[USER_NAME]',$user['username'],$body);
$body = str_replace('[USER_EMAIL]',$user['email'],$body);
$body = str_replace('[USER_PASSWORD]',$user['password'],$body);
@endphp
{!! @$body !!}


@component('mail::button', ['url' => Request::root() . '/login'])
{{ __('Login') }}
@endcomponent

<!-- <p>{{ __('Thank you') }}</p> -->

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
