<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProvisionDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Es un endpoint público
    }

    public function rules(): array
    {
        return [
            'provisioning_token' => ['required', 'string'],
            'chip_id' => ['required', 'string', 'max:255'],
        ];
    }
}