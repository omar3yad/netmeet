<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->longText('content')->nullable();
            $table->timestamps();
        });

        // Insert email templates
        DB::table('email_templates')->insert(array(
            array(
                'name' => 'Meeting Invitation',
                'slug' => 'meeting-invitation',
                'content' => '<p>Greetings!&nbsp;[USER_NAME] has invited you to attend a virtual meeting</p>

<p>&nbsp;</p>

<ul>
    <li><strong>Meeting ID</strong>:&nbsp;[MEETING_ID]</li>
    <li><strong>Title</strong>: [MEETING_TITLE]</li>
    <li><strong>Password</strong>: [MEETING_PASSWORD]</li>
    <li><strong>Date: </strong>[MEETING_DATE]</li>
    <li><strong>Time</strong>: [MEETING_TIME]</li>
    <li><strong>Timezone: </strong>[MEETING_TIMEZONE]</li>
    <li><strong>Description: </strong>[MEETING_DESCRIPTION]</li>
</ul>

<p>&nbsp;</p>

<p>Thank You!</p>'
            ),
            array(
                'name' => 'Cancel Subscription',
                'slug' => 'cancel-subscription',
                'content' => '<p>Subscription cancelled</p>
<p>&nbsp;</p>                
<p>The subscription was cancelled</p>                
<p>&nbsp;</p>                
<p>Thank you</p>'
            ),
            array(
                'name' => 'Payment Status - Success',
                'slug' => 'payment-status-sucess',
                'content' => '<p>Payment completed</p>
<p>The payment was successful</p>                
<p>Thank You</p>'
            ),
            array(
                'name' => 'Payment Status - Fail',
                'slug' => 'payment-status-fail',
                'content' => '<p>Payment cancelled</p>
<p>The payment was cancelled</p>                
<p>Thank You</p>'
            ),
            array(
                'name' => 'Test SMTP',
                'slug' => 'test-smtp',
                'content' => '<p>Hi admin, this is just a test email. Your SMTP is working fine</p>'
            ),
            array(
                'name' => 'Two Factore Auth Code',
                'slug' => 'two-factor-auth-code',
                'content' => '<p>Hi,</p>
<p>&nbsp;</p>                
<p>Your code is : [CODE]</p>                
<p>&nbsp;</p>                
<p>Thank you</p>'
            ),
            array(
                'name' => 'User Creation',
                'slug' => 'user-creation',
                'content' => '<p>Greetings! You can now host meetings</p>
<p>&nbsp;</p>                
<ul>
<li>
<p><strong>Username: </strong>[USER_NAME]</p>
</li>
<li>
<p><strong>Email: </strong>[USER_EMAIL]</p>
</li>
<li>
<p><strong>Password: </strong>[USER_PASSWORD]</p>
</li>
</ul>                
<p>&nbsp;</p>
<p>Thank you</p>'
            )
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_templates');
    }
};
