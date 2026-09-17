<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\EmailTemplates;

class PaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var
     */
    public $payment;
    public $body;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($payment)
    {
        if($payment->status == 'completed'){
            $emailBody = EmailTemplates::where('slug','payment-status-sucess')->first();
        }else{
            $emailBody = EmailTemplates::where('slug','payment-status-fail')->first();
        }
        $this->payment = $payment;
        $this->body = $emailBody['content'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($address = env('MAIL_FROM_ADDRESS'), $name = env('MAIL_FROM_NAME'))
            ->subject($this->payment->status == 'completed' ? __('Payment completed') : __('Payment cancelled') . ' | ' .getSetting('APPLICATION_NAME'))
            ->markdown($this->payment->status == 'completed' ? 'vendor.mail.html.payment-success' : 'vendor.mail.html.payment-fail');
    }
}
