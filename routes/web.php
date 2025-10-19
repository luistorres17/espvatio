<?php

use App\Livewire\DeviceManager; // <--- Añadir esta línea
use Illuminate\Support\Facades\Route;
use App\Actions\CalculateGlobalTenantMetricsAction;
use App\Livewire\DeviceDashboard; // <-- Importar
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // INICIO: Modificación Tarea 3.12
    Route::get('/dashboard', function (CalculateGlobalTenantMetricsAction $action) {
        
        $team = Auth::user()->currentTeam;
        $metrics = $action->execute($team);

        return view('dashboard', [
            'totalMonthlyConsumptionKwh' => $metrics['total_monthly_consumption_kwh'],
            'totalMonthlyCostEstimate' => $metrics['total_monthly_cost_estimate'],
        ]);

    })->name('dashboard');
    
    // --- AÑADIR ESTA LÍNEA ---
    Route::get('/devices', DeviceManager::class)->name('devices.index');
    Route::get('/devices/{device}', DeviceDashboard::class)->name('devices.show');
});