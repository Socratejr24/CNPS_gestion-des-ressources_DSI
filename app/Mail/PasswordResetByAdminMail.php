<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetByAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nom,
        public string $prenom,
        public string $passwordPlain,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre mot de passe a été réinitialisé — CNPS DSI',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.password_reset_by_admin',
            with: [
                'nom'          => $this->nom,
                'prenom'       => $this->prenom,
                'password'     => $this->passwordPlain,
            ],
        );
    }
}