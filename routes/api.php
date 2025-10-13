<?php

// routes/api.php

use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\ProvisioningTokenController; // <--- Importar
use App\Http\Controllers\Api\ProvisionController; // <-- Importar
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ...
Route::post('/provision', [ProvisionController::class, 'store'])->name('devices.provision');
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    //Route::apiResource('devices', DeviceController::class);
    
    // Ruta para generar tokens de aprovisionamiento
    Route::post('provisioning-tokens', [ProvisioningTokenController::class, 'store'])->name('provisioning-tokens.store');

});