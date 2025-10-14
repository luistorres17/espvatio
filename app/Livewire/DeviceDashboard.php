<?php

namespace App\Livewire;

use App\Actions\FetchDeviceMeasurementsAction;
use App\Models\Device;
use Livewire\Component;

class DeviceDashboard extends Component
{
    public Device $device;
    public $measurements;

    /**
     * Se ejecuta al cargar el componente.
     * Recibe el dispositivo desde la ruta.
     */
    public function mount(Device $device, FetchDeviceMeasurementsAction $fetcher)
    {
        $this->device = $device;
        
        // Llamamos a la acción para obtener los datos
        $this->measurements = $fetcher->execute($device);
    }

    public function render()
    {
        return view('livewire.device-dashboard')->layout('layouts.app');
    }
}