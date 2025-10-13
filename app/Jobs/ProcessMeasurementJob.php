<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Device;
use App\Models\Measurement;
use App\Models\ProvisioningToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpMqtt\Client\MqttClient; // Para publicar la respuesta

class ProcessMeasurementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $topic;
    protected $payload;

    public function __construct(string $topic, string $payload)
    {
        $this->topic = $topic;
        $this->payload = $payload;
    }

    public function handle(): void
    {
        $identifier = Str::of($this->topic)->after('devices/')->before('/measurements')->toString();
        $data = json_decode($this->payload, true);

        // Intentamos encontrar un dispositivo existente por su serial_number (que es el ChipID)
        $device = Device::withoutGlobalScopes()->where('serial_number', $identifier)->first();

        // --- FLUJO DE APROVISIONAMIENTO (SI EL DISPOSITIVO NO EXISTE) ---
        if (!$device) {
            $this->provisionDevice($identifier, $data);
            return;
        }

        // --- FLUJO DE TELEMETRÍA NORMAL (SI EL DISPOSITIVO YA EXISTE) ---
        $this->recordMeasurement($device, $data);
    }

    /**
     * Maneja el aprovisionamiento de un nuevo dispositivo.
     */
    protected function provisionDevice(string $tempToken, array $data): void
    {
        Log::info("Iniciando flujo de aprovisionamiento para el token temporal: {$tempToken}");

        // 1. Validar que el payload contenga el chip_id
        if (!isset($data['chip_id'])) {
            Log::warning("Mensaje de aprovisionamiento para [{$tempToken}] no contiene 'chip_id'. Mensaje descartado.");
            return;
        }
        $chipId = $data['chip_id'];

        // 2. Validar el token de aprovisionamiento temporal
        $provisioningToken = ProvisioningToken::withoutGlobalScopes()
            ->where('token', $tempToken)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$provisioningToken) {
            Log::warning("Token de aprovisionamiento inválido, expirado o ya usado: {$tempToken}");
            return;
        }

        // 3. Crear el nuevo dispositivo usando el ChipID como serial_number permanente
        $device = Device::create([
            'team_id' => $provisioningToken->team_id,
            'name' => 'Device ' . $chipId,
            'serial_number' => $chipId, // El ChipID es el nuevo ID permanente
            'status' => 'active',
        ]);

        // 4. Invalidar el token temporal
        $provisioningToken->update(['used_at' => now()]);

        Log::info("Aprovisionamiento exitoso para ChipID [{$chipId}]. Nuevo Device ID: {$device->id}");

        // 5. Publicar el ID permanente de vuelta al dispositivo
        $this->publishProvisioningResponse($tempToken, $chipId);
    }

    /**
     * Registra una nueva medición para un dispositivo existente.
     */
    protected function recordMeasurement(Device $device, array $data): void
    {
        try {
            Measurement::create([
                'device_id' => $device->id,
                'time' => now(),
                'voltage' => $data['voltage'] ?? null,
                'current' => $data['current'] ?? null,
                'power' => isset($data['voltage'], $data['current']) ? $data['voltage'] * $data['current'] : null,
            ]);
        } catch (\Exception $e) {
            Log::error("Error al guardar medición para dispositivo {$device->id}: " . $e->getMessage());
            $this->fail($e);
        }
    }

    /**
     * Publica el ID permanente de vuelta al dispositivo a través de MQTT.
     */
    protected function publishProvisioningResponse(string $tempToken, string $permanentId): void
    {
        try {
            $mqtt = new MqttClient(config('mqtt.connections.default.host'), config('mqtt.connections.default.port'));
            $mqtt->connect();
            
            $responseTopic = "devices/provision/{$tempToken}/response";
            $payload = json_encode(['permanent_id' => $permanentId]);
            
            $mqtt->publish($responseTopic, $payload, 0);
            $mqtt->disconnect();
            
            Log::info("Respuesta de aprovisionamiento enviada a [{$responseTopic}]");
        } catch (\Exception $e) {
            Log::error("Fallo al publicar respuesta de aprovisionamiento: " . $e->getMessage());
        }
    }
}