<?php

namespace App\Actions;

use App\Models\Team;
use Illuminate\Support\Facades\DB;

class CalculateGlobalTenantMetricsAction
{
    /**
     * Calcula las métricas globales de consumo y costo para un tenant (equipo).
     *
     * @param Team $team
     * @return array
     */
    public function execute(Team $team): array
    {
        // Obtener los IDs de todos los dispositivos asociados a este equipo
        $deviceIds = $team->devices()->pluck('id');

        if ($deviceIds->isEmpty()) {
            return [
                'total_monthly_consumption_kwh' => 0,
                'total_monthly_cost_estimate' => 0,
            ];
        }

        // Consulta SQL optimizada para TimescaleDB para agregar el consumo
        // de múltiples dispositivos durante el mes actual.
        $query = "
            SELECT sum(bucket_energy) / 1000.0 as total_kwh
            FROM (
                SELECT
                    avg(power) * (10.0/60.0) as bucket_energy
                FROM measurements
                WHERE device_id = ANY(?) -- Usar ANY() para array de IDs en PostgreSQL
                AND time >= ?
                GROUP BY time_bucket('10 minutes', time), device_id
            ) as per_device_energy
        ";

        // --- INICIO: CORRECCIÓN TAREA 3.12 ---
        // Convertir el array de PHP [1, 2, 3] al formato string de array
        // de PostgreSQL '{1,2,3}' para el binding de ANY().
        $bindings = [
            '{' . $deviceIds->implode(',') . '}', // Ej. '{1,2,3}'
            now()->startOfMonth()                 // Inicio del mes calendario actual
        ];
        // --- FIN: CORRECCIÓN TAREA 3.12 ---

        // Ejecutar la consulta
        $result = DB::selectOne($query, $bindings);

        $totalMonthlyKwh = $result->total_kwh ?? 0;

        // Calcular el costo
        $costPerKwh = $team->cost_per_kwh ?? 0;
        $totalMonthlyCost = $totalMonthlyKwh * $costPerKwh;

        return [
            'total_monthly_consumption_kwh' => round($totalMonthlyKwh, 3),
            'total_monthly_cost_estimate' => round($totalMonthlyCost, 2),
        ];
    }
}