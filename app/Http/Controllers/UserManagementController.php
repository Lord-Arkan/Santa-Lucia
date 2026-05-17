<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserProfileImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function __construct(private readonly UserProfileImageService $imageService)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Users/Index', [
            'users' => User::query()
                ->latest()
                ->get()
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'rol' => $user->rol,
                    'profile_photo_path' => $user->profile_photo_path,
                    'profile_photo_url' => $user->profile_photo_url,
                    'created_at' => $user->created_at?->format('d/m/Y'),
                ]),
            'roles' => $this->roles(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'rol' => $validated['rol'],
        ]);

        if ($request->hasFile('image')) {
            $user->forceFill([
                'profile_photo_path' => $this->imageService->store($request->file('image'), $user),
            ])->save();
        }

        return back()->with('status', 'Usuario creado correctamente.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'rol' => $validated['rol'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('image')) {
            $data['profile_photo_path'] = $this->imageService->store($request->file('image'), $user);
        }

        $user->update($data);

        return back()->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if(auth()->id() === $user->id, 422, 'No puedes eliminar tu propio usuario.');

        $this->imageService->delete($user->profile_photo_path);
        $user->delete();

        return back()->with('status', 'Usuario eliminado correctamente.');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function roles(): array
    {
        return [
            ['value' => 'administrador', 'label' => 'Administrador'],
            ['value' => 'asistente', 'label' => 'Asistente'],
            ['value' => 'doctor', 'label' => 'Doctor'],
            ['value' => 'contador', 'label' => 'Contador'],
            ['value' => 'jefe_area', 'label' => 'Jefe de area'],
        ];
    }
}
