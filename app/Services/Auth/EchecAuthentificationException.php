<?php

namespace App\Services\Auth;

use RuntimeException;

/**
 * Échec de connexion. Le message est destiné à l'utilisateur : il ne doit pas
 * révéler si l'identifiant existe (message générique sauf compte désactivé).
 */
class EchecAuthentificationException extends RuntimeException {}
