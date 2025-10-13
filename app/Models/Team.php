<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// --- PASO 1: ASEGÚRATE DE IMPORTAR LA CLASE CORRECTA ---
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'personal_team',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
        ];
    }

    /**
     * Get all of the devices for the team.
     */
    // --- PASO 2: CORRIGE EL TIPO DE RETORNO AQUÍ ---
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    /**
     * Get all of the provisioning tokens for the team.
     */
    // --- PASO 3: Y TAMBIÉN AQUÍ ---
    public function provisioningTokens(): HasMany
    {
        return $this->hasMany(ProvisioningToken::class);
    }
}