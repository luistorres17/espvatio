<?php

namespace App\Livewire;

use App\Actions\CreateProvisioningToken; // <-- 1. Importar la Acción
use App\Models\Device; // <-- Importar el modelo
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class DeviceManager extends Component
{
    public $showingAddDeviceModal = false;
    public $provisioningToken = null;
    // --- NUEVAS PROPIEDADES PARA LA ELIMINACIÓN ---
    public $confirmingDeviceDeletion = false;
    public $deviceToDeleteId = null;

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
    public function confirmDeviceDeletion($deviceId)
    {
        $this->confirmingDeviceDeletion = true;
        $this->deviceToDeleteId = $deviceId;
    }
    public function deleteDevice()
    {
        if ($this->deviceToDeleteId) {
            // Usamos la relación para asegurarnos de que el usuario solo borre sus propios dispositivos.
            Auth::user()->currentTeam->devices()->where('id', $this->deviceToDeleteId)->firstOrFail()->delete();
        }

        $this->confirmingDeviceDeletion = false;
        $this->deviceToDeleteId = null;
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