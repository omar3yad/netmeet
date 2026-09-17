<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>Invoice</title>

    <!-- Favicon -->
    <link rel="icon" href="./images/favicon.png" type="image/x-icon" />

    <!-- Invoice styling -->
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            text-align: center;
            color: #777;
        }

        body h1 {
            font-weight: 300;
            margin-bottom: 0px;
            padding-bottom: 0px;
            color: #000;
        }

        body h3 {
            font-weight: 300;
            margin-top: 10px;
            margin-bottom: 20px;
            font-style: italic;
            color: #555;
        }

        body a {
            color: #06f;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
            border-collapse: collapse;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }

        .print {
            cursor: pointer;
            background-color: #EC6367;
            border-color: #EC6367;
            color: #fff;
            margin-bottom: 10px;
            border: 1px solid transparent;
            border-radius: .25rem;
            font-size: 1rem;
            font-weight: 500;
            line-height: 1.5;
            padding: .375rem .75rem;
            text-align: center;
            transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        }

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="col-auto">
        <button class="print" onclick="window.print();">{{ __('Print') }}</button>
    </div>
    <div class="invoice-box">
        <table>
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                <img src="{{ asset('storage/images/PRIMARY_LOGO.png') }}" alt="Company logo" style="width: 40%;" />
                            </td>

                            <td>
                                Invoice #: {{ $payments->id }}<br />
                                @php
                                    $createdAt = date('M-d-Y', strtotime($payments->created_at));
                                    $createdAt = explode('-', $createdAt);
                                @endphp
                                Created: {{ $createdAt[0] }} {{ $createdAt[1] }}, {{ $createdAt[2] }}<br />
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                @if ($commonData['COMPANY_NAME'] != '')
                                    {{ $commonData['COMPANY_NAME'] }},<br />
                                    {{ $commonData['COMPANY_ADDRESS'] != '' ? $commonData['COMPANY_ADDRESS'] . ',' : '' }}<br />
                                    {{ $commonData['COMPANY_CITY'] != '' ? $commonData['COMPANY_CITY'] . ',' : '' }}
                                    {{ $commonData['COMPANY_STATE'] != '' ? $commonData['COMPANY_STATE'] : '' }}
                                    {{ $commonData['COMPANY_STATE'] != '' ? $commonData['COMPANY_POSTAL_CODE'] . ',' : '' }}<br />
                                    {{ $commonData['COMPANY_EMAIL'] != '' ? $commonData['COMPANY_EMAIL'] : '' }}<br />
                                @endif
                            </td>

                            @php
                                $customerDetails = json_decode($payments->customer, true);
                            @endphp
                            <td>
                                {{ $customerDetails['name'] }},<br />
                                {{ $customerDetails['address'] }},<br />
                                {{ $customerDetails['city'] }},
                                {{ $customerDetails['state'] != '' ? $customerDetails['state'] . ',' : '' }}
                                {{ $customerDetails['postal_code'] }}<br />
                                {{ $userEmail }}<br />
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>


            <tr class="heading">
                <td>{{ __('Description') }}</td>
                <td>{{ __('Date') }}</td>
                <td>{{ __('Amount') }}</td>
            </tr>
            <tr class="item">

                <td>{{ $payments->product->name }}
                    ({{ isset($payments->product->amount_month) ? 'Monthly' : 'Yearly' }}) </td>
                <td>{{ $createdAt[0] }} {{ $createdAt[1] }}, {{ $createdAt[2] }}</td>
                <td>{{ isset($payments->product->amount_month) ? formatMoney($payments->product->amount_month, $payments->product->currency) : formatMoney($payments->product->amount_year, $payments->product->currency) }}
                    {{ $payments->product->currency }}</td>
            </tr>
            @if ($taxAmount != 0)
                <tr class="coupon">
                    <td></td>
                    <td>{{ $payments->tax_rates[0]->name }} ({{ $payments->tax_rates[0]->percentage . ' %' }}
                        {{ $payments->tax_rates[0]->type == 0 ? 'incl.' : 'excl.' }})</td>
                    <td>{{ formatMoney($taxAmount, $payments->product->currency) }} {{ $payments->product->currency }}
                    </td>
                </tr>
            @endif
            @if ($payments->coupon)
                <tr class="coupon">
                    <td></td>
                    <td>{{ $payments->coupon->code }} ({{ $payments->coupon->percentage . '% Discount' }})</td>
                    <td>{{ formatMoney($discountAmt, $payments->product->currency) }}
                        {{ $payments->product->currency }} </td>
                </tr>
            @endif
            <tr class="total">
                <td></td>
                <td><b>{{ __('Total') }}:</b></td>
                <td>{{ formatMoney($payments->amount, $payments->product->currency) }}
                    {{ $payments->product->currency }}</td>
            </tr>
        </table>
    </div>
</body>

</html>
