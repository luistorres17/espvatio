<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Device;
use App\Models\Measurement;
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

        // Buscar dispositivo por su ID permanente (ChipID)
        $device = Device::withoutGlobalScopes()->where('serial_number', $chipId)->first();

        if (!$device) {
            Log::warning("Rechazando telemetría de dispositivo no registrado: {$chipId}");
            return;
        }

        $data = json_decode($this->payload, true);

        Measurement::create([
            'device_id' => $device->id,
            'time' => now(),
            'voltage' => $data['voltage'] ?? null,
            'current' => $data['current'] ?? null,
            'power' => isset($data['voltage'], $data['current']) ? $data['voltage'] * $data['current'] : null,
        ]);
    }
}