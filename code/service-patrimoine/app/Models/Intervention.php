<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Intervention extends Model
{
    public const STATUTS = ['EN_COURS', 'TERMINEE', 'ANNULEE'];

    protected $attributes = [
        'statut' => 'EN_COURS',
        'type' => 'REPARATION',
    ];

    protected $fillable = [
        'bien_id',
        'type',
        'description',
        'prestataire',
        'cout',
        'etat_precedent',
        'statut',
        'date_debut',
        'date_fin',
        'commentaire',
        'auteur_id',
    ];

    protected function casts(): array
    {
        return [
            'cout' => 'decimal:2',
            'date_debut' => 'date',
            'date_fin' => 'date',
        ];
    }

    public function bien(): BelongsTo
    {
        return $this->belongsTo(Bien::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }

    public function estEnCours(): bool
    {
        return $this->statut === 'EN_COURS';
    }
}
