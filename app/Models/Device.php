<?php

// app/Models/Device.php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'serial_number',
        'status',
    ];

    /**
     * The "booted" method of the model.
     * Aplica un scope global para el aislamiento de datos (multi-tenancy).
     */
    protected static function booted(): void
    {
        static::addGlobalScope('team', function (Builder $builder) {
            if (Auth::check() && Auth::user()->currentTeam) {
                $builder->where('team_id', Auth::user()->currentTeam->id);
            }
        });
    }

    /**
     * Get the team that the device belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get all of the measurements for the device.
     */
    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class);
    }
}