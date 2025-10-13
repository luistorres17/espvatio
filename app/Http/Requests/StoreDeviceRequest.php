<?php

// app/Http/Requests/StoreDeviceRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorización se maneja por el middleware y los Global Scopes
    }

    public function rules(): array
    {
        $teamId = $this->user()->currentTeam->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'serial_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('devices')->where('team_id', $teamId), // Único por tenant
            ],
        ];
    }
}