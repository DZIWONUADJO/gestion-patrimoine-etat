<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BienDocument extends Model
{
    protected $fillable = [
        'bien_id',
        'nom',
        'chemin',
        'mime_type',
        'taille',
        'uploade_par',
    ];

    protected function casts(): array
    {
        return [
            'taille' => 'integer',
        ];
    }

    public function bien(): BelongsTo
    {
        return $this->belongsTo(Bien::class);
    }

    public function uploadeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploade_par');
    }
}
