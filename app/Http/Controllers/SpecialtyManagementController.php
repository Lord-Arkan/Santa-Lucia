<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpecialtyRequest;
use App\Http\Requests\UpdateSpecialtyRequest;
use App\Models\Specialty;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SpecialtyManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Specialty::query();

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $specialties = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $specialties->getCollection()->transform(fn (Specialty $specialty) => [
            'specialty_id' => $specialty->specialty_id,
            'name' => $specialty->name,
            'status' => $specialty->status,
            'created_at' => $specialty->created_at?->format('d/m/Y H:i'),
            'updated_at' => $specialty->updated_at?->format('d/m/Y H:i'),
        ]);

        return Inertia::render('Specialties/Index', [
            'specialties' => $specialties,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function store(StoreSpecialtyRequest $request): RedirectResponse
    {
        Specialty::query()->create([
            'name' => $request->validated('name'),
            'status' => $request->validated('status') ?? 'activo',
        ]);

        return back()->with('status', 'Especialidad creada correctamente.');
    }

    public function update(UpdateSpecialtyRequest $request, Specialty $specialty): RedirectResponse
    {
        $specialty->update([
            'name' => $request->validated('name'),
            'status' => $request->validated('status') ?? 'activo',
        ]);

        return back()->with('status', 'Especialidad actualizada correctamente.');
    }

    public function toggleStatus(Request $request, Specialty $specialty): RedirectResponse
    {
        $specialty->status = $specialty->status === 'activo' ? 'inactivo' : 'activo';
        $specialty->save();

        $message = $specialty->status === 'activo'
            ? 'Especialidad habilitada correctamente.'
            : 'Especialidad inhabilitada correctamente.';

        return back()->with('status', $message);
    }

    public function destroy(Specialty $specialty): RedirectResponse
    {
        try {
            $specialty->delete();
        } catch (QueryException) {
            return back()->withErrors([
                'specialty' => 'No se puede eliminar una especialidad vinculada a doctores o servicios.',
            ]);
        }

        return back()->with('status', 'Especialidad eliminada correctamente.');
    }
}
