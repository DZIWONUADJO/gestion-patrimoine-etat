<?php

namespace App\Http\Requests;

use App\Models\Bien;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BienRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $bien = $this->route('bien');
        $required = $bien instanceof Bien ? 'sometimes' : 'required';

        return [
            'numero_inventaire' => [
                $required, 'string', 'max:100',
                Rule::unique('biens', 'numero_inventaire')->ignore($bien?->getKey()),
            ],
            'designation' => [$required, 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'categorie_id' => [$required, 'integer', 'exists:categories,id'],
            'service_id' => [$required, 'integer', 'exists:services,id'],
            'etat' => ['sometimes', 'required', 'string', Rule::in(Bien::ETATS)],
            'localisation' => ['sometimes', 'nullable', 'string', 'max:255'],
            'marque' => ['sometimes', 'nullable', 'string', 'max:100'],
            'modele' => ['sometimes', 'nullable', 'string', 'max:100'],
            'numero_serie' => ['sometimes', 'nullable', 'string', 'max:100'],
            'date_acquisition' => ['sometimes', 'nullable', 'date'],
            'valeur_acquisition' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'fournisseur' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
