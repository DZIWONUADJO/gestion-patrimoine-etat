<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $attributes = [
        'is_active' => true,
    ];

    protected $fillable = [
        'code_service',
        'nom_service',
        'type',
        'responsable',
        'contact',
        'parent_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function biens(): HasMany
    {
        return $this->hasMany(Bien::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function wouldCreateCycle(?int $parentId): bool
    {
        $visited = [];

        while ($parentId !== null) {
            if ($parentId === (int) $this->getKey() || isset($visited[$parentId])) {
                return true;
            }

            $visited[$parentId] = true;
            $parentId = self::query()->whereKey($parentId)->value('parent_id');
            $parentId = $parentId === null ? null : (int) $parentId;
        }

        return false;
    }
}
