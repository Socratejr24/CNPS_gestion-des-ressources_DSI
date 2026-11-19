<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nom,
        public string $prenom,
        public string $otp,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Réinitialisation de mot de passe — CNPS DSI',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.otp_reset',
            with: [
                'nom' => $this->nom,
                'prenom' => $this->prenom,
                'otp' => $this->otp,
            ],
        );
    }
}