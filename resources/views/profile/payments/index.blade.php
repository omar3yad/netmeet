@extends('profile.index')
@include('include.message')

@section('profile-content')
    @if (count($payments))
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped table-hover table-responsive">
                <thead>
                    <tr>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Plan') }}</th>
                        <th>{{ __('Coupon') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Currency') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Gateway') }}</th>
                        <th>{{ __('Transaction ID') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Transaction Date') }}</th>
                        <th>{{ __('Invoice') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $key => $value)
                        <tr>
                            <td>{{ $value->id }}</td>
                            <td>{{ $value->plan->name }}</td>
                            <td>{{ $value->coupon ? $value->coupon->name : '-' }}</td>
                            <td>{{ $value->amount }}</td>
                            <td>{{ $value->currency }}</td>
                            <td>{{ $value->interval }}</td>
                            <td>{{ $value->gateway }}</td>
                            <td>{{ $value->payment_id }}</td>
                            <td>{{ $value->status }}</td>
                            <td>{{ $value->created_at }}</td>
                            <th><a href="{{ route('profile.invoice',$value->id) }}" target="_blank"><i class="fa fa-eye info" title="{{ __('Click to view invoice.') }}"></i></a></th>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('Plan') }}</th>
                        <th>{{ __('Coupon') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Currency') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Gateway') }}</th>
                        <th>{{ __('Transaction ID') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Transaction Date') }}</th>
                        <th>{{ __('Invoice') }}</th>
                    </tr>
                </tfoot>
            </table>
            <div class="card-footer">
                <div class="float-right">
                    {{ $payments->links() }}
                </div>
            </div>
        </div>
    </div>
    @else
        <p>{{ __('Your payment history will appear here.') }}</p>
    @endif
@endsection
