<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class AuthHelper
{
    /**
     * Génère un code OTP à 6 chiffres.
     */
    public static function generateOtp(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Génère un mot de passe fort et lisible.
     */
    public static function generatePassword(int $length = 12): string
    {
        // Utilisation de Str::password de Laravel 10+ qui est très propre
        return Str::password($length, symbols: true, spaces: false);
    }

    /**
     * Génère un identifiant unique (ex: jkouassi4821).
     */
    public static function generateIdentifiant(string $prenom, string $nom): string
    {
        $base = strtolower(substr($prenom, 0, 1) . $nom);
        $base = preg_replace('/[^a-z0-9]/', '', $base);

        do {
            $identifiant = $base . random_int(1000, 9999);
        } while (\App\Models\User::where('identifiant', $identifiant)->exists());

        return $identifiant;
    }
}