<?php

namespace App\Actions\Jetstream;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException; // Asegurar que esta importación existe
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Jetstream\Contracts\CreatesTeams;
use Laravel\Jetstream\Events\AddingTeam;
use Laravel\Jetstream\Jetstream;

class CreateTeam implements CreatesTeams
{
    /**
     * Validate and create a new team for the given user.
     *
     * @param  array<string, string>  $input
     */
    public function create(User $user, array $input): Team
    {
        // --- INICIO: VALIDACIÓN TAREA 6.2 ---
        // Solo los usuarios Super Admin pueden crear equipos adicionales.
        // Los usuarios normales crean su equipo personal automáticamente al registrarse.
        if (! $user->is_admin) {
            throw new AuthorizationException(
                'No tiene permisos para crear equipos adicionales.' // Mensaje ajustado
            );
        }
        // --- FIN: VALIDACIÓN TAREA 6.2 ---

        // Nota: El Gate::authorize original ya no es estrictamente necesario
        // si solo admins pueden llegar aquí, pero se mantiene por coherencia
        // con la estructura original de Jetstream o por si la política cambia.
        Gate::forUser($user)->authorize('create', Jetstream::newTeamModel());

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('createTeam');

        AddingTeam::dispatch($user);

        $user->switchTeam($team = $user->ownedTeams()->create([
            'name' => $input['name'],
            'personal_team' => false, // Los equipos creados manualmente no son personales
        ]));

        return $team;
    }
}