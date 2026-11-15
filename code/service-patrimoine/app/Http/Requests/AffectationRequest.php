<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AffectationRequest extends FormRequest
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
        return [
            'service_destinataire_id' => ['required', 'integer', 'exists:services,id'],
            'localisation_destinataire' => ['sometimes', 'nullable', 'string', 'max:255'],
            'motif' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'date_effet' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
