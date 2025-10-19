<?php

namespace App\Actions;

use App\Models\Device;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FetchDeviceMeasurementsAction
{
    /**
     * Ejecuta la consulta para obtener las mediciones y cálculos de consumo de un dispositivo.
     *
     * @param Device $device
     * @param string $timeframe (ej. '24h', '7d', '1m')
     * @return array
     */
    public function execute(Device $device, string $timeframe = '24h'): array
    {
        // --- 1. DATOS PARA EL GRÁFICO (LÓGICA EXISTENTE) ---
        $interval = '5 minutes';

        $measurements = DB::table('measurements')
            ->select(
                DB::raw("time_bucket('{$interval}', time) AS bucket"),
                DB::raw('AVG(power) as value')
            )
            ->where('device_id', $device->id)
            ->where('time', '>', now()->subHours(24))
            ->groupBy('bucket')
            ->orderBy('bucket', 'asc')
            ->get();

        $formattedMeasurements = $measurements->map(function ($measurement) {
            return [
                'x' => $measurement->bucket,
                'y' => round($measurement->value, 2),
            ];
        });

        // --- 2. CÁLCULO DE CONSUMO DIARIO (KWH) ---
        // Se aproxima la integral de la potencia agrupando en buckets de 1 minuto.
        // Energía (Wh) = Potencia Media (W) * Duración del bucket (en horas)
        // Para 1 minuto, la duración es 1/60 horas.
        $dailyResult = DB::selectOne("
            SELECT sum(bucket_energy) / 1000.0 as total_kwh FROM (
                SELECT
                    avg(power) / 60.0 as bucket_energy
                FROM measurements
                WHERE device_id = ? AND time >= ?
                GROUP BY time_bucket('1 minute', time)
            ) as per_minute_energy
        ", [$device->id, now()->startOfDay()]);

        $dailyConsumptionKwh = $dailyResult->total_kwh ?? 0;

        // --- 3. CÁLCULO DE CONSUMO MENSUAL (KWH) ---
        // Se utiliza un bucket de 10 minutos para optimizar la consulta mensual.
        // La duración del bucket es 10/60 horas.
        $monthlyResult = DB::selectOne("
            SELECT sum(bucket_energy) / 1000.0 as total_kwh FROM (
                SELECT
                    avg(power) * (10.0/60.0) as bucket_energy
                FROM measurements
                WHERE device_id = ? AND time >= ?
                GROUP BY time_bucket('10 minutes', time)
            ) as per_10_minute_energy
        ", [$device->id, now()->startOfMonth()]);

        $monthlyConsumptionKwh = $monthlyResult->total_kwh ?? 0;

        // --- 4. CÁLCULO DE COSTO MENSUAL ESTIMADO ---
        // Nota: Se asume que el campo 'cost_per_kwh' existe en el modelo Team,
        // según la Tarea 3.7 completada previamente.
        $costPerKwh = $device->team->cost_per_kwh ?? 0;
        $monthlyCostEstimate = $monthlyConsumptionKwh * $costPerKwh;

        // --- 5. RETORNAR RESULTADO COMPLETO ---
        return [
            'measurements' => $formattedMeasurements,
            'daily_consumption_kwh' => round($dailyConsumptionKwh, 3),
            'monthly_consumption_kwh' => round($monthlyConsumptionKwh, 3),
            'monthly_cost_estimate' => round($monthlyCostEstimate, 2),
        ];
    }
}