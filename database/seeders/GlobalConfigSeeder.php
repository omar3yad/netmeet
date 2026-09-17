<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GlobalConfig;
use Illuminate\Support\Str;

class GlobalConfigSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    GlobalConfig::create([
      'key' => 'APPLICATION_NAME',
      'value' => 'JupiterMeet'
    ]);

    GlobalConfig::create([
      'key' => 'PRIMARY_COLOR',
      'value' => '#EC6367'
    ]);

    GlobalConfig::create([
      'key' => 'PRIMARY_LOGO',
      'value' => 'PRIMARY_LOGO.png'
    ]);

    GlobalConfig::create([
      'key' => 'SECONDARY_LOGO',
      'value' => 'SECONDARY_LOGO.png'
    ]);

    GlobalConfig::create([
      'key' => 'FAVICON',
      'value' => 'FAVICON.png'
    ]);

    GlobalConfig::create([
      'key' => 'SIGNALING_URL',
      'value' => 'https://yourdomain.in:9006'
    ]);

    GlobalConfig::create([
      'key' => 'STUN_URL',
      'value' => 'stun:stun.l.google.com:19302'
    ]);

    GlobalConfig::create([
      'key' => 'TURN_URL',
      'value' => 'turn:yourdomain.in'
    ]);

    GlobalConfig::create([
      'key' => 'TURN_USERNAME',
      'value' => 'username'
    ]);

    GlobalConfig::create([
      'key' => 'TURN_PASSWORD',
      'value' => 'password'
    ]);

    GlobalConfig::create([
      'key' => 'DEFAULT_USERNAME',
      'value' => 'Guest'
    ]);

    GlobalConfig::create([
      'key' => 'COOKIE_CONSENT',
      'value' => 'enabled'
    ]);
    
    GlobalConfig::create([
      'key' => 'LANDING_PAGE',
      'value' => 'enabled'
    ]);

    GlobalConfig::create([
      'key' => 'GOOGLE_ANALYTICS_ID',
      'value' => 'null'
    ]);

    GlobalConfig::create([
      'key' => 'SOCIAL_INVITATION',
      'value' => 'Hey, check out this amazing website, where you can host video meetings!'
    ]);

    GlobalConfig::create([
      'key' => 'MODERATOR_RIGHTS',
      'value' => 'enabled'
    ]);

    GlobalConfig::create([
      'key' => 'AUTH_MODE',
      'value' => 'enabled'
    ]);

    GlobalConfig::create([
      'key' => 'PAYMENT_MODE',
      'value' => 'disabled'
    ]);
    
    GlobalConfig::create([
      'key' => 'REGISTRATION',
      'value' => 'enabled'
    ]);
    
    GlobalConfig::create([
      'key' => 'VERIFY_USERS',
      'value' => 'disabled'
    ]);

    GlobalConfig::create([
      'key' => 'STRIPE_KEY',
      'value' => 'pk_test_example'
    ]);

    GlobalConfig::create([
      'key' => 'STRIPE_SECRET',
      'value' => 'sk_test_example'
    ]);

    GlobalConfig::create([
      'key' => 'STRIPE',
      'value' => '0'
    ]);

    GlobalConfig::create([
      'key' => 'STRIPE_WH_SECRET',
      'value' => ''
    ]);

    GlobalConfig::create([
      'key' => 'PAYPAL',
      'value' => '0'
    ]);

    GlobalConfig::create([
      'key' => 'PAYPAL_MODE',
      'value' => 'sandbox'
    ]);

    GlobalConfig::create([
      'key' => 'PAYPAL_CLIENT_ID',
      'value' => ''
    ]);

    GlobalConfig::create([
      'key' => 'PAYPAL_SECRET',
      'value' => ''
    ]);

    GlobalConfig::create([
      'key' => 'PAYPAL_WEBHOOK_ID',
      'value' => ''
    ]);
    
    GlobalConfig::create([
      'key' => 'END_URL',
      'value' => '/pages/thank-you'
    ]);
    
    GlobalConfig::create([
      'key' => 'CUSTOM_JS',
      'value' => ''
    ]);
    
    GlobalConfig::create([
      'key' => 'CUSTOM_CSS',
      'value' => ''
    ]);

    GlobalConfig::create([
      'key' => 'MAIL_MAILER',
      'value' => 'smtp'
    ]);
    
    GlobalConfig::create([
      'key' => 'MAIL_HOST',
      'value' => 'localhost'
    ]);
    
    GlobalConfig::create([
      'key' => 'MAIL_PORT',
      'value' => '1025'
    ]);
    
    GlobalConfig::create([
      'key' => 'MAIL_USERNAME',
      'value' => ''
    ]);
    
    GlobalConfig::create([
      'key' => 'MAIL_PASSWORD',
      'value' => ''
    ]);
    
    GlobalConfig::create([
      'key' => 'MAIL_ENCRYPTION',
      'value' => ''
    ]);
    
    GlobalConfig::create([
      'key' => 'MAIL_FROM_ADDRESS',
      'value' => 'admin@example.com'
    ]);
    
    GlobalConfig::create([
      'key' => 'API_TOKEN',
      'value' => Str::random(60)
    ]);

    GlobalConfig::create([
      'key' => 'VERSION',
      'value' => '2.9.3'
    ]);
  }
}
