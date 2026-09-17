<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('admin') }}" class="brand-link">
        <img src="{{ asset('storage/images/SECONDARY_LOGO.png') }}" alt="{{ getSetting('APPLICATION_NAME') }}"
            class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">{{ getSetting('APPLICATION_NAME') }}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('admin') }}" class="nav-link" data-name="dashboard">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            {{ __('Dashboard') }}
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('meetings') }}" class="nav-link" data-name="meetings">
                        <i class="nav-icon fa fa-video"></i>
                        <p>
                            {{ __('Meetings') }}
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('users') }}" class="nav-link" data-name="users">
                        <i class="nav-icon fa fa-users"></i>
                        <p>
                            {{ __('Users') }}
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('global-config') }}" class="nav-link" data-name="global-config">
                        <i class="nav-icon fa fa-cog"></i>
                        <p>
                            {{ __('Global Configuration') }}
                        </p>
                    </a>
                </li>

                @if (in_array($path, [
                        'admin.plans',
                        'admin.plans.new',
                        'admin.plans.edit',
                        'admin.coupons',
                        'admin.coupons.new',
                        'admin.coupons.edit',
                        'admin.tax_rates',
                        'admin.tax_rates.new',
                        'admin.tax_rates.edit',
                        'admin.transaction',
                        'admin.payment_gateways',
                    ]))
                    <li class="nav-item has-treeview menu-open">
                    @else
                    <li class="nav-item has-treeview">
                @endif
                <a href="#" class="nav-link">
                    <i class="nav-icon fa fa-money-check-alt"></i>
                    <p>
                        {{ __('Manage Payment') }}
                        <i class="right fas fa-angle-right"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    <li class="nav-item">
                        <a href="{{ route('admin.payment_gateways') }}" class="nav-link"
                            data-name="payment-gateways">
                            <i class="nav-icon fa fa-coins"></i>
                            <p>
                                {{ __('Payment Gateways') }}
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.plans') }}" class="nav-link" data-name="plans">
                            <i class="nav-icon fa fa-list-alt"></i>
                            <p>
                                {{ __('Plans') }}
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.coupons') }}" class="nav-link" data-name="coupons">
                            <i class="nav-icon fa fa-tags"></i>
                            <p>
                                {{ __('Coupons') }}
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.tax_rates') }}" class="nav-link" data-name="tax-rates">
                            <i class="nav-icon fa fa-percentage"></i>
                            <p>
                                {{ __('Tax Rates') }}
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.transaction') }}" class="nav-link" data-name="transaction">
                            <i class="nav-icon fa fa-file-invoice-dollar"></i>
                            <p>
                                {{ __('Transaction') }}
                            </p>
                        </a>
                    </li>
                </ul>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.emailTemplates') }}" class="nav-link"
                        data-name="email-templates">
                        <i class="nav-icon fa fa-envelope"></i>
                        <p>
                            {{ __('Email Templates') }}
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('languages') }}" class="nav-link" data-name="languages">
                        <i class="nav-icon fa fa-language"></i>
                        <p>
                            {{ __('Languages') }}
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('signaling') }}" class="nav-link" data-name="signaling">
                        <i class="nav-icon fa fa-signal"></i>
                        <p>
                            {{ __('Signaling Server') }}
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pages') }}" class="nav-link" data-name="pages">
                        <i class="nav-icon fa fa-file-alt"></i>
                        <p>
                            {{ __('Pages') }}
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('activity-log') }}" class="nav-link" data-name="activity-log">
                        <i class="nav-icon fa fa-history"></i>
                        <p>
                            {{ __('Activity Logs') }}
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('update') }}" class="nav-link" data-name="update">
                        <i class="nav-icon fa fa-download"></i>
                        <p>
                            {{ __('Manage Update') }}
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('license') }}" class="nav-link" data-name="license">
                        <i class="nav-icon fa fa-id-badge"></i>
                        <p>
                            {{ __('Manage License') }}
                        </p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>