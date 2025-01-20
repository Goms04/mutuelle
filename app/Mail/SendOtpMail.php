<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;

    
    public function __construct($otp)
    {
        //
        $this->otp = $otp;
    }

    
    /* public function build()
    {
        return $this->subject('Votre code OTP')
            ->view('emails.otp')
            ->with($this->otp);
    } */


    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre code OTP',
        );
    }

    
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }

    
    public function attachments(): array
    {
        return ['otp', $this->otp];
    }
}
