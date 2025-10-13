<?php

// app/Models/ProvisioningToken.php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class ProvisioningToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'token',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
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
     * Get the team that the token belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}