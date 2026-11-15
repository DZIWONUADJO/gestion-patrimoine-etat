<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Affectation extends Model
{
    public const STATUTS = [
        'EN_ATTENTE',
        'VALIDEE',
        'REJETEE',
        'ANNULEE',
    ];

    protected $attributes = [
        'statut' => 'EN_ATTENTE',
    ];

    protected $fillable = [
        'bien_id',
        'service_origine_id',
        'service_destinataire_id',
        'localisation_origine',
        'localisation_destinataire',
        'motif',
        'statut',
        'date_effet',
        'date_fin',
        'date_demande',
        'date_validation',
        'demandeur_id',
        'valideur_id',
        'motif_decision',
    ];

    protected function casts(): array
    {
        return [
            'date_effet' => 'date',
            'date_fin' => 'date',
            'date_demande' => 'datetime',
            'date_validation' => 'datetime',
        ];
    }

    public function bien(): BelongsTo
    {
        return $this->belongsTo(Bien::class);
    }

    public function serviceOrigine(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_origine_id');
    }

    public function serviceDestinataire(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_destinataire_id');
    }

    public function demandeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'demandeur_id');
    }

    public function valideur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valideur_id');
    }

    public function estEnAttente(): bool
    {
        return $this->statut === 'EN_ATTENTE';
    }

    public function estActive(): bool
    {
        return $this->statut === 'VALIDEE' && $this->date_fin === null;
    }
}
