<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventaireLigne extends Model
{
    public const STATUTS = ['PRESENT', 'INTROUVABLE', 'ANOMALIE'];

    protected $fillable = [
        'inventaire_id',
        'bien_id',
        'statut',
        'commentaire',
        'releve_par',
    ];

    public function inventaire(): BelongsTo
    {
        return $this->belongsTo(Inventaire::class);
    }

    public function bien(): BelongsTo
    {
        return $this->belongsTo(Bien::class);
    }

    public function releveur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'releve_par');
    }
}
