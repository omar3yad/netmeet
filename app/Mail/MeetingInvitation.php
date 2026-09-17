<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MeetingInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $meeting;
    public $body;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($meeting, $body)
    {
        $this->meeting = $meeting;
        $this->body = $body;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($address = env('MAIL_FROM_ADDRESS'), $name = env('MAIL_FROM_NAME'))
                    ->subject(getSetting('APPLICATION_NAME') . ' | ' .  __('You have been invited to a meeting'))
                    ->markdown('vendor.mail.html.meetings-invite');
    }
}
