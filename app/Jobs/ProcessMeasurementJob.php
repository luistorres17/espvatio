<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Device;
use App\Models\Measurement;
use App\Models\ProvisioningToken; // Importar el modelo del token
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
        // 1. Extraer el token/serial_number del topic
        $serialNumber = Str::of($this->topic)->after('devices/')->before('/measurements')->toString();

        if (empty($serialNumber)) {
            Log::warning("No se pudo extraer el serial_number del topic: {$this->topic}");
            return;
        }

        // 2. Buscar si el dispositivo ya existe en la base de datos
        $device = Device::withoutGlobalScopes()->where('serial_number', $serialNumber)->first();

        // 3. Si el dispositivo NO existe, intentar aprovisionarlo
        if (!$device) {
            Log::info("Dispositivo [{$serialNumber}] no encontrado. Intentando aprovisionamiento...");

            // Buscar un token de aprovisionamiento válido
            $provisioningToken = ProvisioningToken::withoutGlobalScopes()
                ->where('token', $serialNumber)
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->first();

            // Si no se encuentra un token válido, se ignora el mensaje
            if (!$provisioningToken) {
                Log::warning("Token de aprovisionamiento inválido o expirado para: {$serialNumber}");
                return;
            }

            // Si el token es válido, creamos el nuevo dispositivo
            $device = Device::create([
                'team_id' => $provisioningToken->team_id,
                'name' => 'Device ' . Str::limit($serialNumber, 8), // Un nombre por defecto
                'serial_number' => $serialNumber, // El token se convierte en el serial_number
                'status' => 'active',
            ]);

            // Marcamos el token como usado para que no se pueda reutilizar
            $provisioningToken->update(['used_at' => now()]);

            Log::info("¡Aprovisionamiento exitoso! Nuevo dispositivo creado con ID: {$device->id} para el equipo: {$device->team_id}");
        }

        // 4. Decodificar y almacenar la medición (para dispositivos nuevos y existentes)
        $data = json_decode($this->payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning("Payload JSON inválido para el dispositivo {$device->id}: {$this->payload}");
            return;
        }

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
}