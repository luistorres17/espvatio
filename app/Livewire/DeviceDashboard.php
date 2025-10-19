<?php

namespace App\Livewire;

use App\Actions\FetchDeviceMeasurementsAction;
use App\Models\Device;
use Livewire\Component;
use Livewire\Attributes\Layout; // <-- 1. Importar el atributo

#[Layout('layouts.app')] // <-- 2. Usar el atributo para definir el layout correcto
class DeviceDashboard extends Component
{
    public Device $device;
    public $measurements = [];
    public string $timeframe = '24h';

    // Propiedades añadidas para la Tarea 3.9
    public $dailyConsumptionKwh = 0;
    public $monthlyConsumptionKwh = 0;
    public $monthlyCostEstimate = 0;

    public function mount(Device $device)
    {
        $this->device = $device;
        $this->fetchData(app(FetchDeviceMeasurementsAction::class));
    }

    /**
     * Obtiene los datos de mediciones y cálculos.
     * Modificado para Tarea 3.9 para desestructurar los resultados.
     */
    public function fetchData(FetchDeviceMeasurementsAction $fetchMeasurements)
    {
        // La acción ahora devuelve un array asociativo (definido en Tarea 3.8)
        $data = $fetchMeasurements->execute($this->device, $this->timeframe);

        // Asignar datos del gráfico
        $this->measurements = $data['measurements'];

        // Asignar nuevas estadísticas (Tarea 3.9)
        $this->dailyConsumptionKwh = $data['daily_consumption_kwh'];
        $this->monthlyConsumptionKwh = $data['monthly_consumption_kwh'];
        $this->monthlyCostEstimate = $data['monthly_cost_estimate'];

        // Enviar solo los datos del gráfico al evento del frontend
        $this->dispatch('data-updated', data: $this->measurements);
    }

    public function render()
    {
        return view('livewire.device-dashboard');
    }
}