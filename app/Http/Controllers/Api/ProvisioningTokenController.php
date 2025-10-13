<?php

// app/Http/Controllers/Api/ProvisioningTokenController.php

namespace App\Http\Controllers\Api;

use App\Actions\CreateProvisioningToken;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProvisioningTokenResource;
use Illuminate\Http\Request;

class ProvisioningTokenController extends Controller
{
    public function store(Request $request, CreateProvisioningToken $creator)
    {
        $token = $creator($request->user()->currentTeam);
        return new ProvisioningTokenResource($token);
    }
}