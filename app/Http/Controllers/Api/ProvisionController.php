<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProvisionDeviceRequest;
use App\Models\Device;
use App\Models\ProvisioningToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ProvisionController extends Controller
{
    public function store(ProvisionDeviceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $tempToken = $validated['provisioning_token'];
        $chipId = $validated['chip_id'];

        // 1. Validar que el token temporal es correcto
        $provisioningToken = ProvisioningToken::where('token', $tempToken)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$provisioningToken) {
            return response()->json(['message' => 'Invalid or expired provisioning token.'], 422);
        }

        // 2. Prevenir registros duplicados
        if (Device::where('serial_number', $chipId)->exists()) {
            return response()->json(['message' => 'Device already provisioned.'], 409);
        }
        
        // 3. Crear el nuevo Device
        Device::create([
            'team_id' => $provisioningToken->team_id,
            'name' => 'Device ' . $chipId,
            'serial_number' => $chipId, // El ChipID es el ID permanente
            'status' => 'active',
        ]);

        // 4. Invalidar el token temporal
        $provisioningToken->update(['used_at' => now()]);
        
        // 5. Devolver una respuesta exitosa
        return response()->json(['message' => 'Device provisioned successfully.'], 200);
    }
}