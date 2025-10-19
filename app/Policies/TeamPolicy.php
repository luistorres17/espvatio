<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // INICIO: Corrección Tarea 5.4
        if ($user->is_admin) {
            return true;
        }
        // FIN: Corrección Tarea 5.4

        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Team $team): bool
    {
        // INICIO: Corrección Tarea 5.4
        if ($user->is_admin) {
            return true;
        }
        // FIN: Corrección Tarea 5.4

        return $user->belongsToTeam($team);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // INICIO: Corrección Tarea 5.4
        if ($user->is_admin) {
            return true;
        }
        // FIN: Corrección Tarea 5.4

        return $user->ownedTeams()->count() < config('jetstream.max_teams', 1);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Team $team): bool
    {
        // INICIO: Corrección Tarea 5.4
        if ($user->is_admin) {
            return true;
        }
        // FIN: Corrección Tarea 5.4

        return $user->ownsTeam($team);
    }

    /**
     * Determine whether the user can add team members.
     */
    public function addTeamMember(User $user, Team $team): bool
    {
        // INICIO: Corrección Tarea 5.4
        if ($user->is_admin) {
            return true;
        }
        // FIN: Corrección Tarea 5.4

        return $user->ownsTeam($team);
    }

    /**
     * Determine whether the user can update team member permissions.
     */
    public function updateTeamMemberRole(User $user, Team $team): bool
    {
        // INICIO: Corrección Tarea 5.4
        if ($user->is_admin) {
            return true;
        }
        // FIN: Corrección Tarea 5.4

        return $user->ownsTeam($team);
    }

    /**
     * Determine whether the user can remove team members.
     */
    public function removeTeamMember(User $user, Team $team): bool
    {
        // INICIO: Corrección Tarea 5.4
        if ($user->is_admin) {
            return true;
        }
        // FIN: Corrección Tarea 5.4

        return $user->ownsTeam($team);
    }

    /**
     * Determine whether the user can invite team members.
     */
    public function inviteTeamMember(User $user, Team $team): bool
    {
        // INICIO: Corrección Tarea 5.4
        if ($user->is_admin) {
            return true;
        }
        // FIN: Corrección Tarea 5.4

        return $user->ownsTeam($team);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Team $team): bool
    {
        // INICIO: Corrección Tarea 5.4
        if ($user->is_admin) {
            return true;
        }
        // FIN: Corrección Tarea 5.4

        return $user->ownsTeam($team) && ! $team->personal_team;
    }
}