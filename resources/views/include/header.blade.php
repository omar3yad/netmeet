<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent" aria-expanded="false"
        aria-label="{{ __('Toggle navigation') }}">
        <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Left Side Of Navbar -->
    <ul class="navbar-nav mr-auto">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                    class="fas fa-bars"></i></a>
        </li>
    </ul>

    <!-- Right Side Of Navbar -->
    <ul class="navbar-nav ml-auto">
        @if (getLanguages()->count() > 1)
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-globe"></i> {{ getSelectedLanguage()->name }}
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    @foreach (getLanguages() as $language)
                        <a class="dropdown-item @if (getSelectedLanguage()->name == $language->name) active @endif"
                            href="{{ route('language', ['locale' => $language->code]) }}">{{ $language->name }}</a>
                    @endforeach
                </div>
            </li>
        @endif

        <!-- Authentication Links -->
        @guest
            @if (Route::has('login'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                </li>
            @endif

            @if (Route::has('register'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                </li>
            @endif
        @else
            @if (getAuthUserInfo('role') == 'admin')
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('admin') }}">{{ __('Admin') }}</a>
                </li>
            @endif

            <li class="nav-item">
                <a class="nav-link" href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
            </li>

            <li class="nav-item dropdown">
                <a id="profileDropdown" class="nav-link dropdown-toggle set-profile" href="#" role="button"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                    {{ getAuthUserInfo('username') }}
                </a>

                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="profileDropdown">
                    <a class="dropdown-item" href="{{ route('profile.profile') }}">
                        {{ __('Profile') }}
                    </a>
                    <a class="dropdown-item" href="{{ route('logout') }}"
                        onclick="event.preventDefault();
                                                                                     document.getElementById('logout-form').submit();">
                        {{ __('Logout') }}
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </li>
        @endguest
    </ul>
</nav>
<!-- /.navbar -->