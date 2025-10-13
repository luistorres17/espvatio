<?php

namespace App\Livewire;

use App\Actions\CreateProvisioningToken; // <-- 1. Importar la Acción
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class DeviceManager extends Component
{
    public $showingAddDeviceModal = false;
    public $provisioningToken = null;

    /**
     * Muestra el modal y genera el token directamente.
     */
    public function showAddDeviceModal(CreateProvisioningToken $creator)
    {
        $this->reset('provisioningToken');
        
        try {
            // 2. Llama a la acción directamente, sin HTTP
            $token = $creator(Auth::user()->currentTeam);
            $this->provisioningToken = $token->token;
        } catch (\Exception $e) {
            Log::error('Error al generar token directamente: ' . $e->getMessage());
            $this->provisioningToken = 'Error al generar el token.';
        }

        $this->showingAddDeviceModal = true;
    }

    /**
     * Renderiza la vista del componente.
     */
    public function render()
    {
        $devices = Auth::user()->currentTeam->devices()->latest()->get();

        return view('livewire.device-manager', [
            'devices' => $devices,
        ])->layout('layouts.app');
    }
}