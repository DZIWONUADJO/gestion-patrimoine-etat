<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:services,id'],
        ]);

        $services = Service::query()->orderBy('nom_service');

        if (array_key_exists('parent_id', $filters)) {
            if ($filters['parent_id'] === null) {
                $services->whereNull('parent_id');
            } else {
                $services->where('parent_id', $filters['parent_id']);
            }
        }

        return response()->json([
            'data' => $services->paginate(50),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code_service' => ['required', 'string', 'max:50', 'unique:services,code_service'],
            'nom_service' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'responsable' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:50'],
            'parent_id' => ['nullable', 'integer', 'exists:services,id'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ]);

        $service = Service::create($validated);

        return response()->json([
            'data' => $this->serviceData($service),
        ], 201);
    }

    public function show(Service $service): JsonResponse
    {
        return response()->json([
            'data' => $this->serviceData($service),
        ]);
    }

    public function update(Request $request, Service $service): JsonResponse
    {
        $validated = $request->validate([
            'code_service' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('services', 'code_service')->ignore($service->getKey()),
            ],
            'nom_service' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'string', 'max:50'],
            'responsable' => ['sometimes', 'nullable', 'string', 'max:255'],
            'contact' => ['sometimes', 'nullable', 'string', 'max:50'],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:services,id'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ]);

        if (array_key_exists('parent_id', $validated)
            && $service->wouldCreateCycle($validated['parent_id'])) {
            throw ValidationException::withMessages([
                'parent_id' => ['Cette modification créerait une boucle dans l’arborescence.'],
            ]);
        }

        $service->update($validated);

        return response()->json([
            'data' => $this->serviceData($service->refresh()),
        ]);
    }

    /**
     * @return array{id: int|string, code_service: string|null, nom_service: string|null, type: string, responsable: string|null, contact: string|null, parent_id: int|null, is_active: bool}
     */
    private function serviceData(Service $service): array
    {
        return [
            'id' => $service->getKey(),
            'code_service' => $service->code_service,
            'nom_service' => $service->nom_service,
            'type' => $service->type,
            'responsable' => $service->responsable,
            'contact' => $service->contact,
            'parent_id' => $service->parent_id,
            'is_active' => $service->is_active,
        ];
    }
}
