<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use App\Models\Specialty;
use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Service::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->query('name').'%');
        }

        if ($request->filled('specialty_id')) {
            $query->whereHas('specialties', function ($q) use ($request) {
                $q->where('specialty_id', $request->query('specialty_id'));
            });
        }

        $services = $query->latest()->paginate(10);

        $services->getCollection()->transform(fn (Service $service) => [
            'service_id' => $service->service_id,
            'name' => $service->name,
            'description' => $service->description,
            'price' => number_format((float) $service->price, 2, '.', ''),
            'duration_minutes' => $service->duration_minutes,
            'specialties' => $service->specialties->pluck('name')->join(', '),
            'specialty_ids' => $service->specialties->pluck('specialty_id')->toArray(),
            'doctor_ids' => $service->doctors->pluck('doctor_id')->toArray(),
            'doctors_count' => $service->doctors->count(),
            'status' => $service->status,
            'created_at' => $service->created_at?->format('d/m/Y'),
        ]);

        $specialties = Specialty::query()->where('status', 'activo')->get();

        $doctors = Doctor::with('user')->get()->map(fn ($d) => [
            'doctor_id' => $d->doctor_id,
            'name' => $d->user?->name,
        ]);

        return Inertia::render('Services/Index', [
            'services' => $services,
            'specialties' => $specialties,
            'doctors' => $doctors,
            'filters' => $request->only(['name', 'specialty_id']),
        ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $service = Service::query()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'duration_minutes' => $validated['duration_minutes'],
            'status' => $validated['status'] ?? 'activo',
        ]);

        $service->specialties()->sync($validated['specialty_ids']);

        if (! empty($validated['doctor_ids'])) {
            $service->doctors()->sync($validated['doctor_ids']);
        }

        return back()->with('status', 'Servicio creado correctamente.');
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $validated = $request->validated();

        $service->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'duration_minutes' => $validated['duration_minutes'],
            'status' => $validated['status'] ?? 'activo',
        ]);

        $service->specialties()->sync($validated['specialty_ids']);
        $service->doctors()->sync($validated['doctor_ids'] ?? []);

        return back()->with('status', 'Servicio actualizado correctamente.');
    }

    public function toggleStatus(Request $request, Service $service): RedirectResponse
    {
        $service->status = $service->status === 'activo' ? 'inactivo' : 'activo';
        $service->save();

        $message = $service->status === 'activo' ? 'Servicio habilitado correctamente.' : 'Servicio inhabilitado correctamente.';

        return back()->with('status', $message);
    }

    public function destroy(Service $service): RedirectResponse
    {
        Service::destroy($service->service_id);

        return back()->with('status', 'Servicio eliminado correctamente.');
    }
}
