<?php

use App\Livewire\DeviceManager; // <--- Añadir esta línea
use Illuminate\Support\Facades\Route;

// ...

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // --- AÑADIR ESTA LÍNEA ---
    Route::get('/devices', DeviceManager::class)->name('devices.index');
});