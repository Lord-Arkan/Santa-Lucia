<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
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

    public function index(Request $request): Response
    {
        $query = User::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->query('name').'%');
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%'.$request->query('email').'%');
        }

        if ($request->filled('rol')) {
            $query->where('rol', $request->query('rol'));
        }

        $users = $query->latest()->paginate(10);

        $users->getCollection()->transform(fn (User $user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'rol' => $user->rol,
            'profile_photo_path' => $user->profile_photo_path,
            'profile_photo_url' => $user->profile_photo_url,
            'status' => $user->status ?? true,
            'created_at' => $user->created_at?->format('d/m/Y'),
        ]);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $this->roles(),
            'filters' => $request->only(['name', 'email', 'rol']),
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

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        // Toggle boolean status
        $user->status = ! ($user->status ?? true);
        $user->save();

        $message = $user->status ? 'Usuario habilitado correctamente.' : 'Usuario inhabilitado correctamente.';

        return back()->with('status', $message);
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
