<?php

use Laravel\Fortify\Features;

return [

    /* ... (otras configuraciones) ... */

    'guard' => 'web',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'lowercase_usernames' => true,
    'home' => '/dashboard',
    'prefix' => '',
    'domain' => null,
    'middleware' => ['web'],
    'limiters' => [
        'login' => 'login',
        'two-factor' => 'two-factor',
    ],
    'views' => true,

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Some of the Fortify features are optional. You may disable the features
    | by removing them from this array. You're free to only remove some of
    | these features or you can even remove all of these if you need to.
    |
    */

    'features' => [
        // INICIO: Modificación Tarea 6.1
        Features::registration(), // <-- Descomentada para habilitar registro público
        // FIN: Modificación Tarea 6.1

        Features::resetPasswords(),
        Features::emailVerification(), // <-- Se mantiene activo
        Features::updateProfileInformation(),
        Features::updatePasswords(),
        Features::twoFactorAuthentication([ // <-- Se mantiene configuración original
            'confirm' => true,
            'confirmPassword' => true,
            // 'window' => 0,
        ]),
    ],

];