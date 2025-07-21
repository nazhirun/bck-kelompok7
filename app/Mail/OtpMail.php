<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Kode OTP yang akan dikirim.
     * 
     * @var string
     */
    public $otp;

    /**
     * Nama pengguna
     * 
     * @var string
     */
    public $name;

    /**
     * Tipe OTP (verification atau reset_password)
     * 
     * @var string
     */
    public $type;

    /**
     * Create a new message instance.
     */
    public function __construct(string $otp, string $name, string $type = 'verification')
    {
        $this->otp = $otp;
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->type === 'reset_password' ? 'Kode Reset Password' : 'Kode Verifikasi OTP';
        
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
