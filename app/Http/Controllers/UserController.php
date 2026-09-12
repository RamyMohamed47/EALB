<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['sometimes', 'string', 'max:30'],
        ]);

        $users = User::query()
            ->when(
                isset($validated['phone']),
                fn ($query) => $query->where('phone', $validated['phone'])
            )
            ->latest()
            ->paginate(15);

        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
            $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'address' => ['nullable', 'string'],
            'job_description' => ['nullable', 'string'],
            'role' => [
                'sometimes',
                 Rule::in([
                    User::ROLE_ADMIN,
                    User::ROLE_CUSTOMER,
                ]),
            ],
            ]);

            $role = $validated['role'] ?? User::ROLE_CUSTOMER;

            unset($validated['role']);


            $user = User::create($validated);
            $user->role = $role;
            $user->save();
            return response()->json([
                'message' => 'User created successfully.',
                'user' => $user,
            ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
            $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => [
                'sometimes',
                'string',
                'max:30',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
            'address' => ['sometimes', 'nullable', 'string'],
            'job_description' => ['sometimes', 'nullable', 'string'],
            'role' => [
                'sometimes',
                Rule::in([
                    User::ROLE_ADMIN,
                    User::ROLE_CUSTOMER,
                ]),
            ],
        ]);

        $role = $validated['role'] ?? null;
        unset($validated['role']);

        $user->update($validated);

        if ($role !== null) {
            $user->role = $role;
            $user->save();
        }

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
        'message' => 'User deleted successfully.',
        ]);
    }
}
