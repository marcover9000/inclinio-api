<?php

/*
 * Credencials del primer admin del sistema. Llegides del .env.
 * Usat exclusivament per AdminSeeder.
 */
return [
    'email' => env('ADMIN_EMAIL'),
    'password' => env('ADMIN_PASSWORD'),
];
