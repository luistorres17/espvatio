<?php

// app/Models/Measurement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Measurement extends Model
{
    use HasFactory;

    // Indicamos que no usamos 'id' autoincremental y definimos la clave primaria compuesta
    public $incrementing = false;
    protected $primaryKey = ['device_id', 'time'];

    // Laravel no soporta claves primarias compuestas de forma nativa para inserciones,
    // pero esto es útil para Eloquent. Las inserciones masivas requerirán atención.

    protected $fillable = [
        'time',
        'device_id',
        'voltage',
        'current',
        'power',
        'frequency',
    ];

    protected $casts = [
        'time' => 'datetime',
    ];

    /**
     * The "booted" method of the model.
     * Filtra las mediciones basándose en el equipo del dispositivo asociado.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('team', function (Builder $builder) {
            if (Auth::check() && Auth::user()->currentTeam) {
                $builder->whereHas('device', function ($query) {
                    $query->where('team_id', Auth::user()->currentTeam->id);
                });
            }
        });
    }

    /**
     * Get the device that the measurement belongs to.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}