<?php

namespace App\Actions;

use App\Models\Device;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB; // <-- Importar DB Facade

class FetchDeviceMeasurementsAction
{
    /**
     * Ejecuta la consulta para obtener las mediciones de un dispositivo.
     *
     * @param Device $device
     * @param string $timeframe (ej. '24h', '7d', '1m')
     * @return Collection
     */
    public function execute(Device $device, string $timeframe = '24h'): Collection
    {
        // --- CONSULTA REAL A TIMESCALEDB ---
        
        $interval = '5 minutes'; // Agrupar datos en intervalos de 5 minutos
        $range = '24 hours';     // Obtener datos de las últimas 24 horas

        $measurements = DB::table('measurements')
            ->select(
                DB::raw("time_bucket('{$interval}', time) AS bucket"),
                DB::raw('AVG(power) as value') // Obtener el promedio de la potencia
            )
            ->where('device_id', $device->id)
            ->where('time', '>', now()->subHours(24))
            ->groupBy('bucket')
            ->orderBy('bucket', 'asc')
            ->get();
            
        // Formatear los datos para ApexCharts (formato x, y)
        return $measurements->map(function ($measurement) {
            return [
                'x' => $measurement->bucket,
                'y' => round($measurement->value, 2), // Redondear a 2 decimales
            ];
        });
    }
}