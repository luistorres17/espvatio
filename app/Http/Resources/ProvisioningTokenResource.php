<?php

// app/Http/Resources/ProvisioningTokenResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvisioningTokenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->token,
            'expires_at' => $this->expires_at->toIso8601String(),
        ];
    }
}