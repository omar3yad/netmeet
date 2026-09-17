<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\MeetingInvitation;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $details;
    public $emails;
    public $body;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details, $emails, $body)
    {
        $this->details = $details;
        $this->emails = $emails;
        $this->body = $body;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach($this->emails as $email) {
            $invitation = new MeetingInvitation($this->details, $this->body);
            Mail::to($email)->queue($invitation);
        }
    }
}
