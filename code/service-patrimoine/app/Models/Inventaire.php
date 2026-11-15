<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventaire extends Model
{
    public const STATUTS = ['EN_COURS', 'CLOTUREE'];

    protected $attributes = [
        'statut' => 'EN_COURS',
    ];

    protected $fillable = [
        'libelle',
        'lieu',
        'date',
        'statut',
        'agent_responsable_id',
        'cree_par',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(InventaireLigne::class);
    }

    public function agentResponsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_responsable_id');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function estEnCours(): bool
    {
        return $this->statut === 'EN_COURS';
    }
}
