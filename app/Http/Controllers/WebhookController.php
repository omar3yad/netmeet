<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Mail\PaymentMail;
use App\Traits\PaymentTrait;
use App\Models\User;
use App\Models\Payment;
use Carbon\Carbon;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Mail\UserCreation;
use App\Models\EmailTemplates;
use App\Models\Meeting;
use Illuminate\Support\Facades\Redirect;

class WebhookController extends Controller
{
    use PaymentTrait;

    /**
     * Handle the Stripe webhook.
     */
    public function stripe(Request $request)
    {
        // Attempt to validate the Webhook
        try {
            $stripeEvent = \Stripe\Webhook::constructEvent($request->getContent(), $request->server('HTTP_STRIPE_SIGNATURE'), getSetting('STRIPE_WH_SECRET'));
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Log::info($e->getMessage());

            return response()->json([
                'status' => 400
            ], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            Log::info($e->getMessage());

            return response()->json([
                'status' => 400
            ], 400);
        }

        // Get the metadata
        $metadata = $stripeEvent->data->object->lines->data[0]->metadata ?? ($stripeEvent->data->object->metadata ?? null);

        if (isset($metadata->user)) {
            if ($stripeEvent->type != 'customer.subscription.created' && stripos($stripeEvent->type, 'customer.subscription.') !== false) {
                // Provide enough time for the subscription created event to be handled
                sleep(3);
            }

            $user = User::where('id', '=', $metadata->user)->first();

            // If a user was found
            if ($user) {
                if ($stripeEvent->type == 'customer.subscription.created') {
                    // If the user previously had a subscription, attempt to cancel it
                    if ($user->plan_subscription_id) {
                        $user->planSubscriptionCancel();
                    }

                    $user->plan_id = $metadata->plan;
                    $user->plan_amount = $metadata->amount;
                    $user->plan_currency = $metadata->currency;
                    $user->plan_interval = $metadata->interval;
                    $user->plan_payment_gateway = 'stripe';
                    $user->plan_subscription_id = $stripeEvent->data->object->id;
                    $user->plan_subscription_status = $stripeEvent->data->object->status;
                    $user->plan_created_at = Carbon::now();
                    $user->plan_recurring_at = $stripeEvent->data->object->current_period_end ? Carbon::createFromTimestamp($stripeEvent->data->object->current_period_end) : null;
                    $user->plan_ends_at = $user->plan_recurring_at;
                    $user->save();

                    // If a coupon was used
                    if (isset($metadata->coupon) && $metadata->coupon) {
                        $coupon = Coupon::find($metadata->coupon);

                        // If a coupon was found
                        if ($coupon) {
                            // Increase the coupon usage
                            $coupon->increment('redeems', 1);
                        }
                    }
                } elseif (stripos($stripeEvent->type, 'customer.subscription.') !== false) {
                    // If the subscription exists
                    if ($user->plan_payment_gateway == 'stripe' && $user->plan_subscription_id == $stripeEvent->data->object->id) {
                        // Update the recurring date
                        if ($stripeEvent->data->object->current_period_end) {
                            $user->plan_recurring_at = Carbon::createFromTimestamp($stripeEvent->data->object->current_period_end);
                        }

                        // Update the subscription status
                        if ($stripeEvent->data->object->status) {
                            $user->plan_subscription_status = $stripeEvent->data->object->status;
                        }

                        // Update the subscription end date
                        if ($stripeEvent->data->object->cancel_at_period_end) {
                            $user->plan_ends_at = Carbon::createFromTimestamp($stripeEvent->data->object->current_period_end);
                        } elseif ($stripeEvent->data->object->cancel_at) {
                            $user->plan_ends_at = Carbon::createFromTimestamp($stripeEvent->data->object->cancel_at);
                        } elseif ($stripeEvent->data->object->canceled_at) {
                            $user->plan_ends_at = Carbon::createFromTimestamp($stripeEvent->data->object->canceled_at);
                        } else {
                            $user->plan_ends_at = null;
                        }

                        // Reset the subscription recurring date
                        if (!empty($user->plan_ends_at)) {
                            $user->plan_recurring_at = null;
                        }

                        if ($user->plan_ends_at = null) {
                            $user->plan_ends_at = $user->plan_recurring_at;
                        }
                        $user->save();
                    }
                } elseif ($stripeEvent->type == 'invoice.paid') {
                    // Make sure the invoice contains the payment id
                    if ($stripeEvent->data->object->charge) {
                        $payment = $this->paymentStore([
                            'user_id' => $user->id,
                            'plan_id' => $metadata->plan,
                            'payment_id' => $stripeEvent->data->object->charge,
                            'gateway' => 'stripe',
                            'amount' => $metadata->amount,
                            'currency' => $metadata->currency,
                            'interval' => $metadata->interval,
                            'status' => 'completed',
                            'coupon' => $metadata->coupon ?? null,
                            'tax_rates' => $metadata->tax_rates ?? null,
                            'customer' => $user->billing_information,
                        ]);

                        // Attempt to send the payment confirmation email
                        try {
                            Mail::to($user->email)->send(new PaymentMail($payment));
                        } catch (\Exception $e) {
                        }
                    } else {
                        return response()->json([
                            'status' => 400
                        ], 400);
                    }
                }
            }
        }

        return response()->json([
            'status' => 200
        ], 200);
    }

    /**
     * Handle the PayPal webhook.
     */
    public function paypal(Request $request)
    {
        $httpClient = new HttpClient(['verify' => false]);

        $httpBaseUrl = 'https://' . (getSetting('PAYPAL_MODE') == 'sandbox' ? 'api-m.sandbox' : 'api-m') . '.paypal.com/';

        // Attempt to retrieve the auth token
        try {
            $payPalAuthRequest = $httpClient->request(
                'POST',
                $httpBaseUrl . 'v1/oauth2/token',
                [
                    'auth' => [getSetting('PAYPAL_CLIENT_ID'), getSetting('PAYPAL_SECRET')],
                    'form_params' => [
                        'grant_type' => 'client_credentials'
                    ]
                ]
            );

            $payPalAuth = json_decode($payPalAuthRequest->getBody()->getContents());
        } catch (BadResponseException $e) {
            Log::info($e->getResponse()->getBody()->getContents());

            return response()->json([
                'status' => 400
            ], 400);
        }

        // Get the payload's content
        $payload = json_decode($request->getContent());

        // Attempt to validate the webhook signature
        try {
            $payPalWHSignatureRequest = $httpClient->request(
                'POST',
                $httpBaseUrl . 'v1/notifications/verify-webhook-signature',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $payPalAuth->access_token,
                        'Content-Type' => 'application/json'
                    ],
                    'body' => json_encode([
                        'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
                        'cert_url' => $request->header('PAYPAL-CERT-URL'),
                        'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
                        'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
                        'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
                        'webhook_id' => getSetting('PAYPAL_WEBHOOK_ID'),
                        'webhook_event' => $payload
                    ])
                ]
            );

            $payPalWHSignature = json_decode($payPalWHSignatureRequest->getBody()->getContents());
        } catch (BadResponseException $e) {
            Log::info($e->getResponse()->getBody()->getContents());

            return response()->json([
                'status' => 400
            ], 400);
        }

        // Check if the webhook's signature status is successful
        if ($payPalWHSignature->verification_status != 'SUCCESS') {
            Log::info('PayPal signature validation failed.');

            return response()->json([
                'status' => 400
            ], 400);
        }

        // Parse the custom metadata parameters
        parse_str($payload->resource->custom_id ?? ($payload->resource->custom ?? null), $metadata);

        if ($metadata) {
            $user = User::where('id', '=', $metadata['user'])->first();

            // If a user was found
            if ($user) {
                if ($payload->event_type == 'BILLING.SUBSCRIPTION.CREATED') {
                    // If the user previously had a subscription, attempt to cancel it
                    if ($user->plan_subscription_id) {
                        $user->planSubscriptionCancel();
                    }

                    $user->plan_id = $metadata['plan'];
                    $user->plan_amount = $metadata['amount'];
                    $user->plan_currency = $metadata['currency'];
                    $user->plan_interval = $metadata['interval'];
                    $user->plan_payment_gateway = 'paypal';
                    $user->plan_subscription_id = $payload->resource->id;
                    $user->plan_subscription_status = $payload->resource->status;
                    $user->plan_created_at = Carbon::now();
                    $user->plan_recurring_at = null;
                    $user->plan_ends_at = null;
                    if (!empty($user->plan_ends_at)) {
                        $user->plan_recurring_at = null;
                    }
                    $user->save();

                    // If a coupon was used
                    if (isset($metadata['coupon']) && $metadata['coupon']) {
                        $coupon = Coupon::find($metadata['coupon']);

                        // If a coupon was found
                        if ($coupon) {
                            // Increase the coupon usage
                            $coupon->increment('redeems', 1);
                        }
                    }
                } elseif (stripos($payload->event_type, 'BILLING.SUBSCRIPTION.') !== false) {
                    // If the subscription exists
                    if ($user->plan_payment_gateway == 'paypal' && $user->plan_subscription_id == $payload->resource->id) {
                        // Update the recurring date
                        if (isset($payload->resource->billing_info->next_billing_time)) {
                            $user->plan_recurring_at = Carbon::create($payload->resource->billing_info->next_billing_time);
                        }

                        // Update the subscription status
                        if (isset($payload->resource->status)) {
                            $user->plan_subscription_status = $payload->resource->status;
                        }

                        // If the subscription has been cancelled
                        if ($payload->event_type == 'BILLING.SUBSCRIPTION.CANCELLED') {
                            // Update the subscription end date
                            $user->plan_ends_at = $user->plan_recurring_at;
                        }

                        if (!empty($user->plan_ends_at)) {
                            // Reset the subscription recurring date
                            $user->plan_recurring_at = null;
                        }
                        if (!empty($user->plan_ends_at)) {
                            $user->plan_recurring_at = null;
                        }
                        $user->save();
                    }
                } elseif ($payload->event_type == 'PAYMENT.SALE.COMPLETED') {
                    $payment = $this->paymentStore([
                        'user_id' => $user->id,
                        'plan_id' => $metadata['plan'],
                        'payment_id' => $payload->resource->id,
                        'gateway' => 'paypal',
                        'amount' => $metadata['amount'],
                        'currency' => $metadata['currency'],
                        'interval' => $metadata['interval'],
                        'status' => 'completed',
                        'coupon' => $metadata['coupon'] ?? null,
                        'tax_rates' => $metadata['tax_rates'] ?? null,
                        'customer' => $user->billing_information,
                    ]);

                    // Attempt to send the payment confirmation email
                    try {
                        Mail::to($user->email)->send(new PaymentMail($payment));
                    } catch (\Exception $e) {
                    }
                }
            }
        }

        return response()->json([
            'status' => 200
        ], 200);
    }

    //handle meeting creation request
    public function meeting(Request $request)
    {
        $user = User::select('id')->where('api_token', $request->api_token)->first();

        if (!$user) {
            return response()->json([
                'status' => 400,
                'data' => 'No user found with that api token.'
            ], 400);
        }

        $request['meeting_id'] = strtolower(Str::random(9));
        $user_id = $user->id;

        try {
            $request->validate([
                'meeting_id' => 'required|unique:meetings',
                'title' => 'required|max:100',
                'description' => 'max:1000',
                'password' => 'max:8',
                'timezone' => 'max:100',
                'date' => 'date_format:Y-m-d',
                'time' => 'date_format:H:i:s',
                'api_token' => 'required',
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'status' => 400,
                'data' => $exception->errors()
            ], 400);
        }

        $allowedMeetings = getUserPlanFeatures($user_id)->meeting_no;
        if ($allowedMeetings != -1 && count($user->meeting) >= $allowedMeetings) {
            return response()->json([
                'status' => 400,
                'data' => 'You have reached the maximum meeting creation limit. Upgrade now!'
            ], 400);
        }

        $meeting = new Meeting();
        $meeting->meeting_id = $request->meeting_id;
        $meeting->title = $request->title;
        $meeting->description = $request->description;
        $meeting->user_id = $user_id;
        $meeting->password = $request->password;
        $meeting->date = $request->date;
        $meeting->time = $request->time;
        $meeting->timezone = $request->timezone;

        if ($meeting->save()) {
            return response()->json([
                'status' => 200,
                'data' => $meeting
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'data' => 'An error occurred, please try again later.'
            ], 400);
        }
    }

    //handle user creation request
    public function user(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string|min:3|max:20|unique:users|alpha_dash',
                'email' => 'required|string|email|max:50|unique:users',
                'password' => 'required|string|min:6|max:50',
                'api_token' => 'required',
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'status' => 400,
                'data' => $exception->errors()
            ], 400);
        }

        if ($request->api_token != getSetting('API_TOKEN')) {
            return response()->json([
                'status' => 400,
                'data' => 'The API Token is invalid.'
            ], 400);
        }

        $user = new User();
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->api_token = Str::random(60);

        if ($user->save()) {
            $emailBody = EmailTemplates::where('slug','user-creation')->first();

            Mail::to($request->email)->send(new UserCreation($request->all(), $emailBody['content']));
            if (getSetting('VERIFY_USERS') == 'enabled') {
                $user->sendEmailVerificationNotification();
            }

            return response()->json([
                'status' => 200,
                'data' => $user
            ], 200);
        } else {
            return response()->json([
                'status' => 400,
                'data' => 'An error occurred, please try again later.'
            ], 400);
        }
    }

    /**
     * Handle the Paystack webhook.
     */
    public function paystack(Request $request)
    {
        $requestData = $request->all();
        $eventType = $requestData['event'];
        Log::info($requestData);
        try {
            $user = User::where('email', '=', $requestData['data']['customer']['email'])->first();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 400,
                'message' => 'User Not Found.'
            ], 400);
        }



        try {
            $Data = $requestData['data'];
            switch ($eventType) {
                case "subscription.create":
                    $planData = $Data['plan'];
                    if ($user->plan_subscription_id) {
                        // Attempt to disable old plan
                        $user->planSubscriptionCancel();
                    }

                    $user->plan_amount = $planData['amount'] / 100;
                    $user->plan_currency = $planData['currency'];
                    $user->plan_interval = $planData['interval'];
                    $user->plan_subscription_id = $Data['subscription_code'];
                    $user->plan_subscription_status = $Data['status'];
                    $user->email_token = $Data['email_token'];
                    $user->plan_created_at = Carbon::now();
                    $user->plan_recurring_at = $Data['next_payment_date'] ? date('Y-m-d h:i:s', strtotime($Data['next_payment_date'])) : null;
                    $user->plan_ends_at = $user->plan_recurring_at;
                    $user->save();
                    break;
                case "subscription.disable":
                    $user->plan_subscription_status = $Data['status'];
                    $user->email_token = $Data['email_token'];
                    $user->plan_recurring_at = $Data['next_payment_date'] ? date('Y-m-d h:i:s', strtotime($Data['next_payment_date'])) : null;
                    $user->plan_ends_at = $user->plan_recurring_at;
                    $user->save();
                    break;
                case "subscription.not_renew":
                    $user->plan_subscription_status = $Data['status'];
                    $user->email_token = $Data['email_token'];
                    $user->plan_recurring_at = $Data['next_payment_date'] ? date('Y-m-d h:i:s', strtotime($Data['next_payment_date'])) : null;
                    $user->plan_ends_at = $user->plan_recurring_at;
                    $user->save();
                    break;
                case "invoice.update":
                    $user->plan_subscription_status = $Data['subscription']['status'];
                    $user->plan_recurring_at = $Data['subscription']['next_payment_date'] ? date('Y-m-d h:i:s', strtotime($Data['subscription']['next_payment_date'])) : null;
                    $user->plan_ends_at = $user->plan_recurring_at;
                    $user->save();
                    break;
                case "invoice.payment_failed":
                    $user->plan_subscription_status = $Data['status'];
                    $user->plan_recurring_at = $Data['subscription']['next_payment_date'] ? date('Y-m-d h:i:s', strtotime($Data['subscription']['next_payment_date'])) : null;
                    $user->plan_ends_at = $user->plan_recurring_at;
                    $user->email_token = $Data['subscription']['email_token'];
                    $user->save();
                    break;
                case "charge.success":
                    $user->plan_id = $Data['metadata']['plan'];
                    $user->plan_payment_gateway = 'paystack';
                    $user->save();
                    $payment = $this->paymentStore([
                        'user_id' => $user->id,
                        'plan_id' => $Data['metadata']['plan'],
                        'payment_id' => $Data['id'],
                        'gateway' => 'paystack',
                        'amount' => $Data['amount'] / 100,
                        'currency' => $Data['currency'],
                        'interval' => $Data['plan']['interval'] == 'annually' ? 'year' : 'month',
                        'status' => 'completed',
                        'coupon' => $Data['metadata']['coupon'] ?? null,
                        'tax_rates' => $Data['metadata']['tax_rates'] ?? null,
                        'customer' => $user->billing_information,
                    ]);

                    // Attempt to send the payment confirmation email
                    try {
                        Mail::to($user->email)->send(new PaymentMail($payment));
                    } catch (\Exception $e) {
                    }
                default:
                    break;
            }

            return response()->json([
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 400,
                'message' => 'Something Went Wrong.'
            ], 400);
        }
    }

    /**
     * Obtain Paystack payment information
     * @return void
     */
    public function handlePaystackGatewayCallback(Request $request)
    {
        try {
            $callbackResponse = callCurlApiRequest('/transaction/verify/' . $request->input('trxref'), 'GET');
            if ($callbackResponse['status'] == true && $callbackResponse['data']['status'] == 'success') {
                return redirect()->route('checkout.complete');
            } else {
                return redirect()->route('checkout.cancelled');
            }
        } catch (\Exception $e) {
            return redirect()->route('checkout.cancelled');
        }
    }

    /**
     * Obtain Mollie payment information
     * @return void
     */
    public function handleMollieGatewayCallback(Request $request)
    {
        sleep(3);
        //check if the payment received than only redirect to success otherwise on cancelled page
        $payment = Payment::where('user_id', $request->user()->id)->latest('created_at')->first();
        if($payment){
            $datetime1 = date_create(date('Y-m-d H:i:s', strtotime($payment->created_at)));
            $datetime2 = date_create(date('Y-m-d H:i:s'));
            $interval = date_diff($datetime1, $datetime2);
            $diff = $interval->format('%i');
        }
        
        if ($payment && $payment->status == 'paid' && $diff <= 5 ) {
            return redirect()->route('checkout.complete');
        } else {
            return redirect()->route('checkout.cancelled');
        }
    }

    /**
     * Handle the Moolie webhook.
    */
    public function mollie(Request $request)
    {
        $requestData = $request->all();
        try {
            $getPayment = callCurlApiRequest('/v2/payments/' . $requestData['id'], 'GET', null,'mollie');
            if ($getPayment['status'] == 'paid') {

                try {
                    $user = User::where('customer_id', '=', $getPayment['metadata']['customerId'])->first();
                } catch (\Exception $e) {
                    Log::info($e->getMessage());
                }

                $interval = $getPayment['metadata']['interval'];

                if (Str::contains($interval, 'month')) {
                    $interval = 'month';
                } else {
                    $interval = 'year';
                }

                $payment = Payment::where('payment_id', $getPayment['id'])->first();

                if (!$payment) {
                    $payment = $this->paymentStore([
                        'user_id' => $user->id,
                        'plan_id' => $getPayment['metadata']['plan'],
                        'payment_id' => $getPayment['id'],
                        'gateway' => 'mollie',
                        'amount' => $getPayment['amount']['value'],
                        'currency' => $getPayment['amount']['currency'],
                        'interval' => $interval,
                        'status' => 'completed',
                        'coupon' => $getPayment['metadata']['coupon'] ?? null,
                        'tax_rates' => $getPayment['metadata']['tax_rates'] ?? null,
                        'customer' => $user->billing_information,
                    ]);

                    // Attempt to send the payment confirmation email
                    try {
                        Mail::to($user->email)->send(new PaymentMail($payment));
                    } catch (\Exception $e) {
                    }

                    if (!isset($getPayment['subscriptionId'])) {

                        try {
                            //create subscription
                            $fields = [
                                "amount" => [
                                    "currency" => $getPayment['amount']['currency'],
                                    "value" => $getPayment['amount']['value'],
                                ],
                                "times" => '',
                                "interval" => $getPayment['metadata']['interval'],
                                "description" => $getPayment['metadata']['plan_name'],
                                "webhookUrl" => route('webhooks.mollie')
                            ];

                            if ($user->plan_subscription_id) {
                                // Attempt to cancel old plan
                                // $user->planSubscriptionCancel();
                                //Attempt to update subscription
                                $subscriptionData = callCurlApiRequest('/v2/customers/' . $getPayment['metadata']['customerId'] . '/subscriptions/' . $user->plan_subscription_id, 'PATCH', $fields,  'mollie');
                            } else {
                                //Attempt to create subscription
                                $subscriptionData = callCurlApiRequest('/v2/customers/' . $getPayment['metadata']['customerId'] . '/subscriptions', 'POST', $fields, 'mollie');
                            }


                            if ($subscriptionData['status'] == 'active') {

                                $user->plan_id = $getPayment['metadata']['plan'];
                                $user->plan_amount = $getPayment['amount']['value'];
                                $user->plan_currency = $getPayment['amount']['currency'];
                                $user->plan_interval = $getPayment['metadata']['interval'];
                                $user->plan_payment_gateway = 'mollie';
                                $user->plan_subscription_id = $subscriptionData['id'];
                                $user->plan_subscription_status = $subscriptionData['status'];
                                $user->plan_created_at = Carbon::now();
                                $user->plan_recurring_at = $subscriptionData['nextPaymentDate'] ? date('Y-m-d h:i:s', strtotime($subscriptionData['nextPaymentDate'])) : null;
                                $user->plan_ends_at = $user->plan_recurring_at;
                                $user->save();
                            } else {
                                $user->plan_subscription_id = $subscriptionData['id'];
                                $user->plan_subscription_status = $subscriptionData['status'];
                                $user->plan_recurring_at = $subscriptionData['nextPaymentDate'] ? date('Y-m-d h:i:s', strtotime($subscriptionData['nextPaymentDate'])) : null;
                                $user->plan_ends_at = $user->plan_recurring_at;
                                $user->save();
                            }
                        } catch (\Exception $e) {
                            Log::info($e->getMessage());
                            return response()->json([
                                'status' => 400,
                                'message' => 'User Not Found.'
                            ], 400);
                        }
                    } else {
                        //get subscription details
                        try {
                            $getSubscription = callCurlApiRequest('/v2/customers/' . $getPayment['metadata']['customerId'] . '/subscriptions/' . $getPayment['subscriptionId'], 'GET', null, 'mollie');
                            $user->plan_subscription_status = $getSubscription['status'];
                            $user->plan_created_at = Carbon::now();
                            $user->plan_recurring_at = $getSubscription['nextPaymentDate'] ? date('Y-m-d h:i:s', strtotime($getSubscription['nextPaymentDate'])) : null;
                            $user->plan_ends_at = $user->plan_recurring_at;
                            $user->save();
                        } catch (\Exception $e) {
                            Log::info($e->getMessage());
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json([
                'status' => 400,
                'message' => 'Somthing went wrong.'
            ], 400);
        }
    }

    /**
     * Handle the Razorpay webhook.
    */
    public function razorpay(Request $request)
    {
        $requestData = $request->all();
        Log::info($requestData['event']);
        Log::info($requestData);
        $eventType = $requestData['event'];

        //added to handle payment gateway exception
        if (!str_contains($eventType,'subscription') ) {
            return response()->json([
                'status' => 200
            ], 200);
        }

        try {
            $user = User::where('id', '=', $requestData['payload']['subscription']['entity']['notes']['customerID'])->first();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 400,
                'message' => 'User Not Found.'
            ], 400);
        }

        try {
            $subscriptionData = $requestData['payload']['subscription']['entity'];
            if($eventType == 'subscription.charged'){
                $paymentData = $requestData['payload']['payment']['entity'];
            }
            switch ($eventType) {
                case "subscription.charged":
                    if($paymentData['status'] != 'captured'){
                        break;
                    }
                    if ($user->plan_subscription_id) {
                        // Attempt to disable old plan
                        $user->planSubscriptionCancel();
                    }
                    $user->plan_id = $subscriptionData['notes']['plan_id'];
                    $user->plan_amount = $paymentData['amount'] / 100;
                    $user->plan_currency = $paymentData['currency'];
                    $user->plan_interval = $subscriptionData['notes']['interval'];
                    $user->plan_subscription_id = $subscriptionData['id'];
                    $user->plan_subscription_status = $subscriptionData['status'];
                    $user->plan_payment_gateway = 'razorpay';
                    $user->email_token = $paymentData['token_id'];
                    $user->plan_created_at = Carbon::now();
                    $user->plan_recurring_at = Carbon::createFromTimestamp($subscriptionData['charge_at'])->toDateTimeString();
                    $user->plan_ends_at = Carbon::createFromTimestamp($subscriptionData['end_at'])->toDateTimeString();
                    $user->save();

                    if($subscriptionData['notes']['interval'] == 'monthly'){
                        $interval = 'month';
                    }else{
                        $interval = 'year';
                    }

                    $payment = $this->paymentStore([
                        'user_id' => $user->id,
                        'plan_id' => $subscriptionData['notes']['plan_id'],
                        'payment_id' => $paymentData['id'],
                        'gateway' => 'razorpay',
                        'amount' => $paymentData['amount'] / 100,
                        'currency' => $paymentData['currency'],
                        'interval' => $interval,
                        'status' => 'completed',
                        'coupon' => $subscriptionData['notes']['coupon'] ?? null,
                        'tax_rates' => $subscriptionData['notes']['tax_rates'] ?? null,
                        'customer' => $user->billing_information,
                    ]);

                    // Attempt to send the payment confirmation email
                    try {
                        Mail::to($user->email)->send(new PaymentMail($payment));
                    } catch (\Exception $e) {
                    }
                    break;
                case "subscription.completed":
                    $user->plan_subscription_status = $subscriptionData['status'];
                    $user->plan_recurring_at = null;
                    $user->plan_ends_at = Carbon::createFromTimestamp($subscriptionData['end_at'])->toDateTimeString();
                    $user->save();
                    break;
                case "subscription.paused":
                    $user->plan_subscription_status = $subscriptionData['status'];
                    $user->plan_ends_at = Carbon::createFromTimestamp($subscriptionData['end_at'])->toDateTimeString();
                    $user->save();
                    break;
                case "subscription.pending":
                    $user->plan_subscription_status = $subscriptionData['status'];
                    $user->plan_recurring_at = Carbon::createFromTimestamp($subscriptionData['charge_at'])->toDateTimeString();
                    $user->plan_ends_at = Carbon::createFromTimestamp($subscriptionData['end_at'])->toDateTimeString();
                    $user->save();
                    break;
                case "subscription.halted":
                    $user->plan_subscription_status = $subscriptionData['status'];
                    $user->plan_recurring_at = Carbon::createFromTimestamp($subscriptionData['charge_at'])->toDateTimeString();
                    $user->plan_ends_at = Carbon::createFromTimestamp($subscriptionData['end_at'])->toDateTimeString();
                    $user->save();
                    break;
                case "subscription.resumed":
                    $user->plan_subscription_status = $subscriptionData['status'];
                    $user->plan_recurring_at = Carbon::createFromTimestamp($subscriptionData['charge_at'])->toDateTimeString();
                    $user->plan_ends_at = Carbon::createFromTimestamp($subscriptionData['end_at'])->toDateTimeString();
                    $user->save();
                    break;
                case "subscription.cancelled":
                    $user->plan_subscription_status = $subscriptionData['status'];
                    $user->plan_recurring_at = null;
                    $user->plan_ends_at = Carbon::createFromTimestamp($subscriptionData['end_at'])->toDateTimeString();
                    $user->save();
                    break;                
                    
                default:
                    break;
            }

            return response()->json([
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            Log::info($e);
            return response()->json([
                'status' => 400,
                'message' => 'Something Went Wrong.'
            ], 400);
        }
    }
}
