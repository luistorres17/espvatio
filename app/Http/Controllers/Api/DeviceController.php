<?php

// app/Http/Controllers/Api/DeviceController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Http\Resources\DeviceResource;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        // El Global Scope ya filtra por el team_id del usuario autenticado
        return DeviceResource::collection($request->user()->currentTeam->devices);
    }

    public function store(StoreDeviceRequest $request)
    {
        $device = $request->user()->currentTeam->devices()->create($request->validated());
        return new DeviceResource($device);
    }

    public function show(Device $device)
    {
        // El Global Scope ya ha verificado que el device pertenece al tenant
        return new DeviceResource($device);
    }

    public function update(UpdateDeviceRequest $request, Device $device)
    {
        $device->update($request->validated());
        return new DeviceResource($device);
    }

    public function destroy(Device $device)
    {
        $device->delete();
        return response()->noContent();
    }
}