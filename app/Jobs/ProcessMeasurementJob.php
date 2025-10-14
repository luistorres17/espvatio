<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Device;
use App\Models\Measurement;
use Illuminate\Support\Facades\Cache; // <-- 1. Importar el Cache
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
    $chipId = Str::of($this->topic)->after('devices/')->before('/measurements')->toString();

    if (empty($chipId)) {
        Log::info("Job descartado: ChipID vacío en el topic '{$this->topic}'.");
        return;
    }

    // 1. Buscar el dispositivo. Si no existe, creamos uno de prueba
    $device = Device::withoutGlobalScopes()->where('serial_number', $chipId)->first();

    if (!$device) {
        // Creamos un dispositivo de prueba con team_id = 1
        $device = Device::create([
            'serial_number' => $chipId,
            'name' => "Dispositivo {$chipId} (Auto)",
            'team_id' => 1, // <- asegúrate de que existe un team con id=1
            'status' => 'provisioned',
        ]);

        Log::warning("Dispositivo {$chipId} no existía, se creó automáticamente con team_id=1");
    }

    // 2. Lógica de Rate Limiting (Guardar cada 5 minutos)
    $lock = Cache::lock('measurement_lock_for_device_' . $device->id, 300);

    if ($lock->get()) {
        Log::info("Lock adquirido para el dispositivo {$device->id}. Procesando medición.");
        
        $data = json_decode($this->payload, true);

        Measurement::create([
            'device_id' => $device->id,
            'time' => now(),
            'voltage' => $data['voltage'] ?? null,
            'current' => $data['current'] ?? null,
            'power' => isset($data['voltage'], $data['current']) ? $data['voltage'] * $data['current'] : null,
        ]);
    } else {
        Log::debug("Lock no adquirido para el dispositivo {$device->id}. Medición ignorada por rate limiting.");
    }
}

}