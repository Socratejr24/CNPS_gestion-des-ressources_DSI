<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PrestataireCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nom,
        public string $prenom,
        public string $identifiant,
        public string $passwordPlain,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vos identifiants de connexion — CNPS DSI',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.prestataire_credentials',
            with: [
                'nom' => $this->nom,
                'prenom' => $this->prenom,
                'identifiant' => $this->identifiant,
                'password' => $this->passwordPlain,
            ],
        );
    }
}