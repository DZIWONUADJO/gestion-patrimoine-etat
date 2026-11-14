<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categorie extends Model
{
    protected $attributes = [
        'is_active' => true,
    ];

    protected $fillable = [
        'libelle',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function biens(): HasMany
    {
        return $this->hasMany(Bien::class);
    }
}
