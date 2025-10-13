<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Conexión por Defecto del Cliente MQTT
    |--------------------------------------------------------------------------
    |
    | Esta es la conexión por defecto que se usará cuando no se especifique una.
    |
    */
    'default_connection' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Conexiones del Cliente MQTT
    |--------------------------------------------------------------------------
    |
    | Aquí puedes definir las configuraciones para cada cliente MQTT que
    | tu aplicación utilizará.
    |
    */
    'connections' => [

        'default' => [
            'host'       => env('MQTT_HOST', '127.0.0.1'),
            'port'       => env('MQTT_PORT', 1883),
            'protocol'   => '3.1', // O '3.1.1', '5.0'
            'client_id'  => env('MQTT_CLIENT_ID'),
            'username'   => env('MQTT_USERNAME'),
            'password'   => env('MQTT_PASSWORD'),

            // Opciones de conexión adicionales
            'connection_settings' => [
                'tls' => [
                    'enabled'               => env('MQTT_TLS_ENABLED', false),
                    'allow_self_signed'     => env('MQTT_TLS_ALLOW_SELF_SIGNED', false),
                    'verify_peer'           => env('MQTT_TLS_VERIFY_PEER', true),
                    'ca_file'               => env('MQTT_TLS_CA_FILE'),
                ],
                'last_will' => [
                    'topic'   => env('MQTT_LAST_WILL_TOPIC'),
                    'message' => env('MQTT_LAST_WILL_MESSAGE'),
                    'qos'     => env('MQTT_LAST_WILL_QOS', 0),
                    'retain'  => env('MQTT_LAST_WILL_RETAIN', false),
                ],
            ],
        ],

    ],
];