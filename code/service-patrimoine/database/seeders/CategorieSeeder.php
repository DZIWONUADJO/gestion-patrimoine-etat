<?php

namespace Database\Seeders;

use App\Models\Categorie;
use Illuminate\Database\Seeder;

class CategorieSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Informatique' => 'Équipements informatiques et réseaux.',
            'Mobilier' => 'Mobilier et équipements de bureau.',
            'Véhicules' => 'Véhicules et moyens de transport de service.',
        ] as $libelle => $description) {
            Categorie::firstOrCreate(
                ['libelle' => $libelle],
                ['description' => $description, 'is_active' => true],
            );
        }
    }
}
