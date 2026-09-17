<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\EmailTemplates;

class UpdateMeetingInvitationEmailBody extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        EmailTemplates::where('slug','meeting-invitation')->update(array(
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
        ));
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
