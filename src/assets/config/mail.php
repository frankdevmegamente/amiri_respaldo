
<?php
// src/assets/config/mail.php
// Load mail configuration from environment variables (fallback to defaults)
return [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'smtp_auth' => true,
    'username' => 'jorge@albardi.mx',       // ← Cambia esto
    'password' => 'rjmgiiefddeiusrl',
    'from_email' => 'no-reply@amiri.com',
    'from_name' => 'Amiri App'
];
