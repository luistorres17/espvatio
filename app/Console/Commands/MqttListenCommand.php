<?php

namespace App\Console\Commands;

use App\Jobs\ProcessMeasurementJob;
use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttListenCommand extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Conecta al broker MQTT y escucha los mensajes';

    public function handle()
    {
        $this->info('Configurando la conexión con el broker MQTT...');

        // Leemos la configuración desde la clave correcta 'connections.default'
        $config = config('mqtt.connections.default');

        $server   = $config['host'];
        $port     = $config['port'];
        $clientId = $config['client_id'];
        $username = $config['username'];
        $password = $config['password'];

        $mqtt = new MqttClient($server, $port, $clientId);

        $connectionSettings = (new ConnectionSettings)
            ->setUsername($username)
            ->setPassword($password);

        $this->info("Conectando a {$server}:{$port}...");
        $mqtt->connect($connectionSettings, true);
        $this->info('¡Conexión exitosa!');

        $mqtt->subscribe('devices/+/measurements', function (string $topic, string $message) {
            $this->info("Mensaje recibido en topic [{$topic}]");
            ProcessMeasurementJob::dispatch($topic, $message);
        }, 1);

        $this->info('Escuchando mensajes. Presiona CTRL+C para detener.');
        $mqtt->loop(true);
        $mqtt->disconnect();
    }
}