<?php

namespace App\Actions\Jetstream;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\UpdatesTeamNames;

class UpdateTeamName implements UpdatesTeamNames
{
    /**
     * Validate and update the given team's name.
     *
     * @param  mixed  $user
     * @param  mixed  $team
     * @param  array  $input
     * @return void
     */
    public function update($user, $team, array $input)
    {
        Gate::forUser($user)->authorize('update', $team);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            // --- AÑADIR ESTA VALIDACIÓN ---
            'cost_per_kwh' => ['nullable', 'numeric', 'min:0'], 
        ])->validateWithBag('updateTeamName');

        $team->forceFill([
            'name' => $input['name'],
            // --- AÑADIR ESTA LÍNEA PARA GUARDAR ---
            'cost_per_kwh' => $input['cost_per_kwh'] ?? null,
        ])->save();
    }
}