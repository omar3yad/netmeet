<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class V292 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insert global configurations
        DB::table('global_config')->insert([
                //paystack payment gateway keys
                [
                    'key' => 'PAYSTACK',
                    'value' => 0
                ],
                [
                    'KEY' => 'PAYSTACK_SECRET_KEY',
                    'value' => ''
                ],

                //company details
                [
                    'KEY' => 'COMPANY_NAME',
                    'value' => ''
                ],
                [
                    'KEY' => 'COMPANY_ADDRESS',
                    'value' => ''
                ],
                [
                    'KEY' => 'COMPANY_CITY',
                    'value' => ''
                ],
                [
                    'KEY' => 'COMPANY_STATE',
                    'value' => ''
                ],
                [
                    'KEY' => 'COMPANY_POSTAL_CODE',
                    'value' => ''
                ],
                [
                    'KEY' => 'COMPANY_COUNTRY',
                    'value' => ''
                ],
                [
                    'KEY' => 'COMPANY_PHONE',
                    'value' => ''
                ],
                [
                    'KEY' => 'COMPANY_EMAIL',
                    'value' => ''
                ],
                [
                    'KEY' => 'COMPANY_TAX_ID',
                    'value' => ''
                ],

                //Mollie payment gateway keys
                [
                    'KEY' => 'MOLLIE',
                    'value' => 0
                ],
                [
                    'KEY' => 'MOLLIE_API_KEY',
                    'value' => ''
                ],

                //Captcha keys
                [
                    'KEY' => 'CAPTCHA_REGISTER_PAGE',
                    'value' => 'disabled'
                ],
                [
                    'KEY' => 'CAPTCHA_LOGIN_PAGE',
                    'value' => 'disabled'
                ],

                //Razorpay payment gateway keys
                [
                    'key' => 'RAZORPAY',
                    'value' => 0
                ],
                [
                    'key' => 'RAZORPAY_SECRET_KEY',
                    'value' => ''
                ],
                [
                    'key' => 'RAZORPAY_API_KEY',
                    'value' => ''
                ],

                //Social login keys
                [
                    'key' => 'GOOGLE_SOCIAL_LOGIN',
                    'value' => 'disabled'
                ],
                [
                    'key' => 'GOOGLE_CLIENT_ID',
                    'value' => ''
                ],
                [
                    'key' => 'GOOGLE_CLIENT_SECRET',
                    'value' => ''
                ],
                [
                    'key' => 'FACEBOOK_SOCIAL_LOGIN',
                    'value' => 'disabled'
                ],
                [
                    'key' => 'FACEBOOK_CLIENT_ID',
                    'value' => ''
                ],
                [
                    'key' => 'FACEBOOK_CLIENT_SECRET',
                    'value' => ''
                ],
                [
                    'key' => 'LINKEDIN_SOCIAL_LOGIN',
                    'value' => 'disabled'
                ],
                [
                    'key' => 'LINKEDIN_CLIENT_ID',
                    'value' => ''
                ],
                [
                    'key' => 'LINKEDIN_CLIENT_SECRET',
                    'value' => ''
                ],
                [
                    'key' => 'TWITTER_SOCIAL_LOGIN',
                    'value' => 'disabled'
                ],
                [
                    'key' => 'TWITTER_CLIENT_ID',
                    'value' => ''
                ],
                [
                    'key' => 'TWITTER_CLIENT_SECRET',
                    'value' => ''
                ],

                //general
                [
                    'key' => 'LIMITED_SCREEN_SHARE',
                    'value' => 'disabled'
                ]
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
