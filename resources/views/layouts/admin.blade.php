@php
    $path = request()->route()->getName();
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ getSelectedLanguage()->direction }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/fa.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/ionicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/jquery-ui.css') }}" rel="stylesheet">
    <link href="{{ asset('css/daterangepicker.css') }}" rel="stylesheet">
    <link href="{{ asset('css/toastr.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin.css?version=') . getVersion() }}" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('storage/images/FAVICON.png') }}">

    @if (getSetting('PWA') == 'enabled')
        <link rel="manifest" href="/manifest.json">
    @endif

    @yield('style')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        @include('include.header')

        @include('include.sidebar')

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            @include('include.content-header')

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
        </div>
        <!-- /.content-wrapper -->

        @include('include.footer')
    </div>

    @if (getSetting('PWA') == 'enabled')
        @include('include.pwa-installation-modal')

        <script type="text/javascript">
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/serviceworker.js', {
                    scope: '.'
                }).then(function(registration) {}, function(err) {});
            }
        </script>
    @endif

    @if (isDemoMode())
        <div id="buy-now">
            <a id="buy-now-link" href="https://codecanyon.net/cart/configure_before_adding/31388330"
                target="_blank"><span>$</span>{{ config('app.script_price') }}</a>
            <button class="buy-now-button" onclick="document.getElementById('buy-now-link').click();">
                {{ __('Buy Now') }}
            </button>
        </div>
    @endif

    <!-- Scripts -->
    <script>
        const pwa = "{{ getSetting('PWA') }}";
        
        const languages = {
            user_registration: "{{ __('User Registration') }}",
            meetings: "{{ __('Meetings') }}",
            error_occurred: "{{ __('An error occurred, please try again') }}",
            data_updated: "{{ __('Data updated successfully') }}",
            valid_license: "{{ __('Your license is valid. Type: ') }}",
            invalid_license: "{{ __('Your license is invalid. Error: ') }}",
            confirmation: "{{ __('Are you sure') }}",
            license_uninstalled: "{{ __('License uninstalled') }}",
            license_uninstalled_failed: "{{ __('License uninstallation failed. Error: ') }}",
            update_available: "{{ __('An update is available: Version: ') }}",
            already_latest_version: "{{ __('Application is already at latest version. Version: ') }}",
            application_updated: "{{ __('The application has been successfully updated to the latest version') }}",
            update_failed: "{{ __('Update failed. Error: ') }}",
            data_added: "{{ __('Data added successfully') }}",
            data_deleted: "{{ __('Data deleted successfully') }}",
            all: "{{ __('All') }}",
            info: "{{ __('Showing page _PAGE_ of _PAGES_') }}",
            lengthMenu: "{{ __('Display _MENU_ records per page') }}",
            zeroRecords: "{{ __('Nothing found - sorry') }}",
            infoEmpty: "{{ __('No records available') }}",
            infoFiltered: "{{ __('filtered from _MAX_ total records') }}",
            next: "{{ __('Next') }}",
            previous: "{{ __('Previous') }}",
            search: "{{ __('Search') }}",
            jan: "{{ __('Jan') }}",
            feb: "{{ __('Feb') }}",
            mar: "{{ __('Mar') }}",
            apr: "{{ __('Apr') }}",
            may: "{{ __('May') }}",
            june: "{{ __('June') }}",
            jul: "{{ __('Jul') }}",
            aug: "{{ __('Aug') }}",
            sep: "{{ __('Sep') }}",
            oct: "{{ __('Oct') }}",
            nov: "{{ __('Nov') }}",
            dec: "{{ __('Dec') }}",
            link_copied: "{{ __('The link has been copied to the clipboard') }}",
            code_copied: "{{ __('The code has been copied to the clipboard') }}",
            token_copied: "{{ __('API Token has been copied to the clipboard') }}",
            free: "{{ __('Free') }}",
            paid: "{{ __('Paid') }}",
            income: "{{ __('Income') }}",
        };
    </script>
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/app.min.js') }}"></script>
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="{{ asset('js/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('js/toastr.min.js') }}"></script>
    <script src="{{ asset('js/common.js?version=') . getVersion() }}"></script>
    @yield('script')
</body>

</html>
