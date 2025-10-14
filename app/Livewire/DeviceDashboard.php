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
    public $measurements;

    public function mount(Device $device, FetchDeviceMeasurementsAction $fetcher)
    {
        $this->device = $device;
        
        try {
            $this->measurements = $fetcher->execute($device);
        } catch (\Exception $e) {
            // Si la consulta a TimescaleDB falla, mostramos un error amigable
            // y evitamos que la página se rompa.
            $this->measurements = collect();
            Log::error('Error al obtener mediciones: ' . $e->getMessage());
            // Opcional: puedes añadir un mensaje de sesión para el usuario.
            // session()->flash('error', 'No se pudieron cargar los datos del gráfico.');
        }
    }

    public function render()
    {
        // 3. Ya no se necesita el método ->layout() aquí
        return view('livewire.device-dashboard');
    }
}