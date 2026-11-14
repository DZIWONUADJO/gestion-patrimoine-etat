<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategorieController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Categorie::query()
                ->orderBy('libelle')
                ->paginate(50),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'libelle' => ['required', 'string', 'max:100', 'unique:categories,libelle'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ]);

        $categorie = Categorie::create($validated);

        return response()->json([
            'data' => $this->categorieData($categorie),
        ], 201);
    }

    public function show(Categorie $categorie): JsonResponse
    {
        return response()->json([
            'data' => $this->categorieData($categorie),
        ]);
    }

    public function update(Request $request, Categorie $categorie): JsonResponse
    {
        $validated = $request->validate([
            'libelle' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'libelle')->ignore($categorie->getKey()),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ]);

        $categorie->update($validated);

        return response()->json([
            'data' => $this->categorieData($categorie->refresh()),
        ]);
    }

    /**
     * @return array{id: int|string, libelle: string|null, description: string|null, is_active: bool}
     */
    private function categorieData(Categorie $categorie): array
    {
        return [
            'id' => $categorie->getKey(),
            'libelle' => $categorie->libelle,
            'description' => $categorie->description,
            'is_active' => $categorie->is_active,
        ];
    }
}
