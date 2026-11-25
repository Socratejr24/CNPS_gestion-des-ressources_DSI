<?php

/*
|--------------------------------------------------------------------------
| Module Demandes
|--------------------------------------------------------------------------
*/

return [

    'notifications' => [
        // SFD §12.3 : notifications internes (toujours actives) et par courrier
        // électronique « selon la configuration retenue ». Les e-mails partent par le
        // mailer configuré (MAIL_MAILER) ; en local, MAIL_MAILER=log les écrit dans le journal.
        'mail' => (bool) env('DEMANDES_NOTIFICATIONS_MAIL', false),
    ],

];
