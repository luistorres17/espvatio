<?php
// app/Actions/CreateProvisioningToken.php

namespace App\Actions;

use App\Models\ProvisioningToken;
use App\Models\Team;
use Illuminate\Support\Str;

class CreateProvisioningToken
{
    public function __invoke(Team $team): ProvisioningToken
    {
        return $team->provisioningTokens()->create([
            'token' => Str::upper(Str::random(8)),
            'expires_at' => now()->addMinutes(15),
        ]);
    }
}