<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class DoctorManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Doctor::query()->with(['user', 'specialty']);

        if ($request->filled('name')) {
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', '%'.$request->query('name').'%'));
        }

        if ($request->filled('license_number')) {
            $query->where('license_number', 'like', '%'.$request->query('license_number').'%');
        }

        $doctors = $query->latest()->paginate(10);

        $doctors->getCollection()->transform(fn (Doctor $doctor) => [
            'doctor_id' => $doctor->doctor_id,
            'user' => $doctor->user ? ['id' => $doctor->user->id, 'name' => $doctor->user->name] : null,
            'specialty' => $doctor->specialty?->name,
            'specialty_id' => $doctor->specialty_id,
            'license_number' => $doctor->license_number,
            'status' => $doctor->status,
            'created_at' => $doctor->created_at?->format('d/m/Y'),
        ]);

        return Inertia::render('Doctors/Index', [
            'doctors' => $doctors,
            'filters' => $request->only(['name', 'license_number']),
            'specialties' => Specialty::query()->where('status', 'activo')->get(),
            'users' => User::query()->where('rol', 'doctor')->select('id', 'name')->get(),
        ]);
    }

    public function store(StoreDoctorRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Ensure the selected user has role 'doctor'
        $role = DB::table('users')->where('id', $validated['user_id'])->value('rol');
        if ($role !== 'doctor') {
            return back()->withErrors(['user_id' => 'El usuario seleccionado debe tener el rol de doctor.']);
        }

        Doctor::query()->create($validated);

        return back()->with('status', 'Doctor creado correctamente.');
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor): RedirectResponse
    {
        $validated = $request->validated();

        // Ensure the selected user has role 'doctor'
        $role = DB::table('users')->where('id', $validated['user_id'])->value('rol');
        if ($role !== 'doctor') {
            return back()->withErrors(['user_id' => 'El usuario seleccionado debe tener el rol de doctor.']);
        }

        $doctor->update($validated);

        return back()->with('status', 'Doctor actualizado correctamente.');
    }

    public function toggleStatus(Request $request, Doctor $doctor): RedirectResponse
    {
        $doctor->status = $doctor->status === 'activo' ? 'inactivo' : 'activo';
        $doctor->save();

        $message = $doctor->status === 'activo' ? 'Doctor habilitado correctamente.' : 'Doctor inhabilitado correctamente.';

        return back()->with('status', $message);
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        Doctor::destroy($doctor->doctor_id);

        return back()->with('status', 'Doctor eliminado correctamente.');
    }
}
