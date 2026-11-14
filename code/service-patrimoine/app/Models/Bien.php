<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bien extends Model
{
    public const ETATS = [
        'BON_ETAT',
        'ETAT_MOYEN',
        'A_REPARER',
        'EN_REPARATION',
        'HORS_SERVICE',
        'INTROUVABLE',
    ];

    protected $attributes = [
        'etat' => 'BON_ETAT',
        'is_active' => true,
    ];

    protected $fillable = [
        'numero_inventaire',
        'designation',
        'description',
        'categorie_id',
        'service_id',
        'etat',
        'localisation',
        'marque',
        'modele',
        'numero_serie',
        'date_acquisition',
        'valeur_acquisition',
        'fournisseur',
        'is_active',
        'archive_le',
    ];

    protected function casts(): array
    {
        return [
            'date_acquisition' => 'date',
            'valeur_acquisition' => 'decimal:2',
            'archive_le' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(BienDocument::class);
    }
}
