<?php

/*
 * Configuració general d'Inclinio. Llegida del .env.
 */
return [
    /*
     * URL base del panell admin. Usada per generar links als emails
     * (password reset, futures notificacions, etc.).
     */
    'admin_url' => env('ADMIN_URL', 'http://admin.inclinio.localhost'),
];
