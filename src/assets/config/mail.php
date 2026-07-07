<?php

// src/assets/config/mail.php

// Load mail configuration from environment variables (fallback to defaults)

return [

    'host' => 'mail.amiri-app.site',

    'port' => 465,

    'smtp_auth' => true,

    'username' => 'admin@amiri-app.site',

    'password' => 'M9T=DS}+J;tYCP^3',

    'encryption' => 'ssl',

    'from_email' => 'admin@amiri-app.site',

    'from_name' => 'Amiri App',

    'smtp_options' => [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]

];
