<?php

namespace App\Notifications;

use App\Models\Demande;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification d'un événement sur une demande (SFD §12).
 *
 * Canaux : base de données (cloche, toujours) et e-mail si activé
 * (config demandes.notifications.mail) et si l'adresse est réelle.
 */
class EvenementDemande extends Notification
{
    public function __construct(
        public readonly Demande $demande,
        public readonly string $titre,
        public readonly string $message,
        public readonly string $icone = 'info',
        public readonly string $couleur = 'blue',
        public readonly ?string $action = null,
    ) {}

    public function via(User $destinataire): array
    {
        $canaux = ['database'];

        if (config('demandes.notifications.mail')
            && filled($destinataire->email)
            && ! str_ends_with($destinataire->email, '@ldap.invalid')) {
            $canaux[] = 'mail';
        }

        return $canaux;
    }

    public function toArray(User $destinataire): array
    {
        return [
            'demande_id' => $this->demande->id,
            'reference' => $this->demande->reference,
            'titre' => $this->titre,
            'message' => $this->message,
            'icone' => $this->icone,
            'couleur' => $this->couleur,
            'action' => $this->action,
        ];
    }

    public function toMail(User $destinataire): MailMessage
    {
        return (new MailMessage)
            ->subject("[{$this->demande->reference}] {$this->titre}")
            ->greeting("Bonjour {$destinataire->prenom},")
            ->line($this->message)
            ->line("Demande : {$this->demande->reference} — {$this->demande->objet}")
            ->action('Ouvrir la demande', route('demandes.show', $this->demande))
            ->salutation('Gestion des ressources DSI — CNPS');
    }
}
