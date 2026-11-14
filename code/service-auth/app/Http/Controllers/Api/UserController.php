<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => User::query()
                ->orderBy('name')
                ->paginate(25),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $role = UserRole::tryFrom((string) $request->input('role'));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'service_id' => [
                Rule::requiredIf($role !== null && $role !== UserRole::Admin),
                Rule::prohibitedIf($role === UserRole::Admin),
                'nullable',
                'integer',
                Rule::exists('services', 'id'),
            ],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => UserRole::from($validated['role']),
            'service_id' => $role === UserRole::Admin
                ? null
                : ($validated['service_id'] ?? null),
            'is_active' => true,
        ]);

        return response()->json([
            'data' => $this->userData($user),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => $this->userData($user),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $requestedRole = UserRole::tryFrom(
            (string) $request->input('role', $user->role->value)
        );

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->getKey()),
            ],
            'password' => ['sometimes', 'required', 'string', 'min:12', 'confirmed'],
            'role' => ['sometimes', 'required', Rule::enum(UserRole::class)],
            'service_id' => [
                Rule::requiredIf(
                    $requestedRole !== null
                    && $requestedRole !== UserRole::Admin
                    && $request->input('service_id', $user->service_id) === null
                ),
                Rule::prohibitedIf($requestedRole === UserRole::Admin),
                'nullable',
                'integer',
                Rule::exists('services', 'id'),
            ],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ]);

        $user = DB::transaction(function () use ($request, $user, $validated): User {
            $user = User::query()
                ->lockForUpdate()
                ->findOrFail($user->getKey());

            $role = UserRole::from($validated['role'] ?? $user->role->value);
            $isActive = (bool) ($validated['is_active'] ?? $user->is_active);
            $isChangingLastAdmin = $user->role === UserRole::Admin
                && $user->is_active
                && ($role !== UserRole::Admin || ! $isActive);

            if ($request->user()->is($user)
                && ($role !== UserRole::Admin || ! $isActive)) {
                throw ValidationException::withMessages([
                    'role' => ['Vous ne pouvez pas désactiver ou rétrograder votre propre compte administrateur.'],
                ]);
            }

            if ($isChangingLastAdmin) {
                $activeAdmins = User::query()
                    ->where('role', UserRole::Admin->value)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->get(['id']);

                if ($activeAdmins->count() <= 1) {
                    throw ValidationException::withMessages([
                        'role' => ['Le dernier administrateur actif ne peut pas être désactivé ou rétrogradé.'],
                    ]);
                }
            }

            $user->fill([
                'name' => $validated['name'] ?? $user->name,
                'email' => $validated['email'] ?? $user->email,
                'password' => $validated['password'] ?? $user->password,
                'role' => $role,
                'service_id' => $role === UserRole::Admin
                    ? null
                    : ($validated['service_id'] ?? $user->service_id),
                'is_active' => $isActive,
            ]);
            $user->save();

            if (! $user->is_active) {
                $user->tokens()->delete();
            }

            return $user->refresh();
        });

        return response()->json([
            'data' => $this->userData($user),
        ]);
    }

    /**
     * @return array{id: int|string, name: string, email: string, role: string, service_id: int|null, is_active: bool}
     */
    private function userData(User $user): array
    {
        return [
            'id' => $user->getKey(),
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'service_id' => $user->service_id,
            'is_active' => $user->is_active,
        ];
    }
}
