<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialty;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const STATUS_LABELS = [
        'SCHEDULED' => 'Pendiente',
        'COMPLETED' => 'Atendida',
        'CANCELLED' => 'Cancelada',
        'NO_SHOW' => 'No asistio',
        'EXPIRED' => 'Vencida',
    ];

    public function index(Request $request): Response
    {
        $filters = $this->validatedFilters($request);

        return Inertia::render('Reports/Index', [
            'filters' => $filters,
            'summary' => $this->summary($filters),
            'charts' => $this->charts($filters),
            'periodReport' => $this->periodReport($filters),
            'doctorReport' => $this->doctorReport($filters),
            'specialtyReport' => $this->specialtyReport($filters),
            'patientHistory' => $this->patientHistory($filters),
            'absenceReport' => $this->absenceReport($filters),
            'newPatientsReport' => $this->newPatientsReport($filters),
            'doctors' => Doctor::query()
                ->with('user')
                ->whereHas('user')
                ->orderBy('doctor_id')
                ->get()
                ->map(fn (Doctor $doctor) => [
                    'doctor_id' => $doctor->doctor_id,
                    'name' => $doctor->user?->name,
                ]),
            'specialties' => Specialty::query()
                ->orderBy('name')
                ->get(['specialty_id', 'name'])
                ->map(fn (Specialty $specialty) => [
                    'specialty_id' => $specialty->specialty_id,
                    'name' => $specialty->name,
                ]),
        ]);
    }

    public function export(Request $request, string $format): StreamedResponse|HttpResponse
    {
        abort_unless(in_array($format, ['csv', 'xlsx', 'pdf'], true), 404);

        $filters = $this->validatedFilters($request);
        $section = $request->validate([
            'section' => ['nullable', Rule::in(['dashboard', 'period', 'doctor', 'specialty', 'patient', 'absences', 'new_patients'])],
        ])['section'] ?? 'dashboard';

        $rows = $this->exportRows($section, $filters);
        $filename = 'reporte-'.$section.'-'.now()->format('Ymd-His');

        if ($format === 'pdf') {
            return response($this->printableHtml($rows, $section), 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
            ]);
        }

        if ($format === 'xlsx') {
            return response()->streamDownload(function () use ($rows, $section) {
                echo $this->excelHtml($rows, $section);
            }, $filename.'.xls', [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            ]);
        }

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            if ($rows->isNotEmpty()) {
                fputcsv($handle, array_keys($rows->first()));
            }

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename.'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'group_by' => ['nullable', Rule::in(['day', 'week', 'month'])],
            'doctor_id' => ['nullable', 'integer', 'exists:doctors,doctor_id'],
            'specialty_id' => ['nullable', 'integer', 'exists:specialties,specialty_id'],
            'patient_search' => ['nullable', 'string', 'max:255'],
        ]);

        return [
            'start_date' => $validated['start_date'] ?? now()->subDays(30)->toDateString(),
            'end_date' => $validated['end_date'] ?? now()->toDateString(),
            'group_by' => $validated['group_by'] ?? 'day',
            'doctor_id' => $validated['doctor_id'] ?? '',
            'specialty_id' => $validated['specialty_id'] ?? '',
            'patient_search' => $validated['patient_search'] ?? '',
        ];
    }

    private function appointmentBase(array $filters): Builder
    {
        $query = Appointment::query();

        $query->whereBetween('appointments.appointment_date', [$filters['start_date'], $filters['end_date']]);

        if ($filters['doctor_id'] !== '') {
            $query->where('appointments.doctor_id', $filters['doctor_id']);
        }

        if ($filters['specialty_id'] !== '') {
            $query->whereHas('doctor', fn (Builder $doctor) => $doctor->where('specialty_id', $filters['specialty_id']));
        }

        if ($filters['patient_search'] !== '') {
            $search = $filters['patient_search'];
            $query->whereHas('patient', function (Builder $patient) use ($search) {
                $patient->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%")
                    ->orWhere('patient_id', $search);
            });
        }

        return $query;
    }

    private function patientBase(array $filters): Builder
    {
        $query = Patient::query()->whereBetween(DB::raw('DATE(created_at)'), [$filters['start_date'], $filters['end_date']]);

        if ($filters['patient_search'] !== '') {
            $search = $filters['patient_search'];
            $query->where(function (Builder $patient) use ($search) {
                $patient->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%")
                    ->orWhere('patient_id', $search);
            });
        }

        return $query;
    }

    private function summary(array $filters): array
    {
        $base = $this->appointmentBase($filters);
        $today = now()->toDateString();

        $statusCounts = (clone $base)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $topDoctor = (clone $base)
            ->select('doctor_id', DB::raw('COUNT(*) as total'))
            ->with('doctor.user')
            ->groupBy('doctor_id')
            ->orderByDesc('total')
            ->first();

        $topSpecialty = (clone $base)
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.doctor_id')
            ->join('specialties', 'doctors.specialty_id', '=', 'specialties.specialty_id')
            ->select('specialties.name', DB::raw('COUNT(*) as total'))
            ->groupBy('specialties.specialty_id', 'specialties.name')
            ->orderByDesc('total')
            ->first();

        return [
            'total_appointments' => (clone $base)->count(),
            'today_appointments' => (clone $base)->whereDate('appointments.appointment_date', $today)->count(),
            'pending_appointments' => (int) ($statusCounts['SCHEDULED'] ?? 0),
            'attended_appointments' => (int) ($statusCounts['COMPLETED'] ?? 0),
            'cancelled_appointments' => (int) ($statusCounts['CANCELLED'] ?? 0),
            'attended_patients' => (clone $base)->where('status', 'COMPLETED')->distinct('patient_id')->count('patient_id'),
            'top_doctor' => $topDoctor?->doctor?->user?->name ?? 'Sin datos',
            'top_doctor_total' => (int) ($topDoctor?->total ?? 0),
            'top_specialty' => $topSpecialty?->name ?? 'Sin datos',
            'top_specialty_total' => (int) ($topSpecialty?->total ?? 0),
        ];
    }

    private function charts(array $filters): array
    {
        $statusRows = $this->appointmentBase($filters)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => self::STATUS_LABELS[$row->status] ?? $row->status,
                'value' => (int) $row->total,
            ]);

        return [
            'status' => $statusRows,
            'period' => $this->periodRows($filters),
            'specialties' => $this->specialtyRows($filters)->take(8)->values(),
        ];
    }

    private function periodReport(array $filters): array
    {
        return [
            'rows' => $this->paginateCollection($this->periodRows($filters), 'period_page'),
        ];
    }

    private function doctorReport(array $filters): array
    {
        return [
            'rows' => $this->paginateCollection($this->doctorRows($filters), 'doctor_page'),
        ];
    }

    private function specialtyReport(array $filters): array
    {
        return [
            'rows' => $this->paginateCollection($this->specialtyRows($filters), 'specialty_page'),
        ];
    }

    private function patientHistory(array $filters): array
    {
        $appointments = $this->appointmentBase($filters)
            ->with(['patient', 'doctor.user', 'doctor.specialty', 'service'])
            ->latest('appointment_date')
            ->paginate(10, ['*'], 'patient_page')
            ->withQueryString();

        $appointments->getCollection()->transform(fn (Appointment $appointment) => [
            'appointment_id' => $appointment->appointment_id,
            'patient' => trim($appointment->patient?->first_name.' '.$appointment->patient?->last_name),
            'document' => $appointment->patient?->document_number,
            'date' => $appointment->appointment_date?->format('d/m/Y'),
            'time' => $this->formatTime($appointment->start_time),
            'doctor' => $appointment->doctor?->user?->name,
            'specialty' => $appointment->doctor?->specialty?->name,
            'reason' => $appointment->service?->name ?? 'No registrado',
            'status' => self::STATUS_LABELS[$appointment->status] ?? $appointment->status,
        ]);

        return ['rows' => $appointments];
    }

    private function absenceReport(array $filters): array
    {
        $appointments = $this->appointmentBase($filters)
            ->with(['patient', 'doctor.user', 'doctor.specialty', 'service'])
            ->whereIn('status', ['CANCELLED', 'NO_SHOW'])
            ->latest('appointment_date')
            ->paginate(10, ['*'], 'absence_page')
            ->withQueryString();

        $appointments->getCollection()->transform(fn (Appointment $appointment) => [
            'appointment_id' => $appointment->appointment_id,
            'patient' => trim($appointment->patient?->first_name.' '.$appointment->patient?->last_name),
            'document' => $appointment->patient?->document_number,
            'date' => $appointment->appointment_date?->format('d/m/Y'),
            'time' => $this->formatTime($appointment->start_time),
            'doctor' => $appointment->doctor?->user?->name,
            'specialty' => $appointment->doctor?->specialty?->name,
            'status' => self::STATUS_LABELS[$appointment->status] ?? $appointment->status,
            'cancellation_reason' => 'No registrado',
        ]);

        $base = $this->appointmentBase($filters);
        $total = (clone $base)->count();
        $absences = (clone $base)->whereIn('status', ['CANCELLED', 'NO_SHOW'])->count();

        return [
            'rows' => $appointments,
            'stats' => [
                'total' => $total,
                'absences' => $absences,
                'rate' => $total > 0 ? round(($absences / $total) * 100, 2) : 0,
            ],
        ];
    }

    private function newPatientsReport(array $filters): array
    {
        return [
            'rows' => $this->paginateCollection($this->patientRows($filters), 'new_patient_page'),
        ];
    }

    private function paginateCollection($rows, string $pageName, int $perPage = 10): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);

        return new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
                'query' => request()->query(),
            ],
        );
    }

    private function periodRows(array $filters)
    {
        [$expression] = $this->dateBucketExpression('appointments.appointment_date', $filters['group_by']);

        return $this->appointmentBase($filters)
            ->selectRaw($expression.' as period')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'SCHEDULED' THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN status = 'COMPLETED' THEN 1 ELSE 0 END) as attended")
            ->selectRaw("SUM(CASE WHEN status = 'CANCELLED' THEN 1 ELSE 0 END) as cancelled")
            ->selectRaw("SUM(CASE WHEN status = 'NO_SHOW' THEN 1 ELSE 0 END) as no_show")
            ->groupBy(DB::raw($expression))
            ->orderBy(DB::raw($expression))
            ->get()
            ->map(fn ($row) => [
                'period' => $row->period,
                'total' => (int) $row->total,
                'pending' => (int) $row->pending,
                'attended' => (int) $row->attended,
                'cancelled' => (int) $row->cancelled,
                'no_show' => (int) $row->no_show,
            ]);
    }

    private function doctorRows(array $filters)
    {
        return $this->appointmentBase($filters)
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.doctor_id')
            ->join('users', 'doctors.user_id', '=', 'users.id')
            ->select('doctors.doctor_id', 'users.name as doctor')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN appointments.status = 'COMPLETED' THEN 1 ELSE 0 END) as attended")
            ->selectRaw("SUM(CASE WHEN appointments.status = 'SCHEDULED' THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN appointments.status = 'CANCELLED' THEN 1 ELSE 0 END) as cancelled")
            ->selectRaw("SUM(CASE WHEN appointments.status = 'NO_SHOW' THEN 1 ELSE 0 END) as no_show")
            ->groupBy('doctors.doctor_id', 'users.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'doctor_id' => $row->doctor_id,
                'doctor' => $row->doctor,
                'total' => (int) $row->total,
                'attended' => (int) $row->attended,
                'pending' => (int) $row->pending,
                'cancelled' => (int) $row->cancelled,
                'no_show' => (int) $row->no_show,
            ]);
    }

    private function specialtyRows(array $filters)
    {
        return $this->appointmentBase($filters)
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.doctor_id')
            ->join('specialties', 'doctors.specialty_id', '=', 'specialties.specialty_id')
            ->select('specialties.specialty_id', 'specialties.name as specialty')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN appointments.status = 'COMPLETED' THEN 1 ELSE 0 END) as attended")
            ->selectRaw("SUM(CASE WHEN appointments.status = 'SCHEDULED' THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN appointments.status = 'CANCELLED' THEN 1 ELSE 0 END) as cancelled")
            ->groupBy('specialties.specialty_id', 'specialties.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'specialty_id' => $row->specialty_id,
                'specialty' => $row->specialty,
                'total' => (int) $row->total,
                'attended' => (int) $row->attended,
                'pending' => (int) $row->pending,
                'cancelled' => (int) $row->cancelled,
            ]);
    }

    private function patientRows(array $filters)
    {
        [$expression] = $this->dateBucketExpression('patients.created_at', $filters['group_by']);

        return $this->patientBase($filters)
            ->selectRaw($expression.' as period')
            ->selectRaw('COUNT(*) as total')
            ->groupBy(DB::raw($expression))
            ->orderBy(DB::raw($expression))
            ->get()
            ->map(fn ($row) => [
                'period' => $row->period,
                'total' => (int) $row->total,
            ]);
    }

    private function dateBucketExpression(string $column, string $groupBy): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return match ($groupBy) {
                'week' => ["strftime('%Y-W%W', {$column})"],
                'month' => ["strftime('%Y-%m', {$column})"],
                default => ["date({$column})"],
            };
        }

        return match ($groupBy) {
            'week' => ["CONCAT(YEAR({$column}), '-W', LPAD(WEEK({$column}, 3), 2, '0'))"],
            'month' => ["DATE_FORMAT({$column}, '%Y-%m')"],
            default => ["DATE({$column})"],
        };
    }

    private function exportRows(string $section, array $filters)
    {
        return match ($section) {
            'period' => $this->periodRows($filters),
            'doctor' => $this->doctorRows($filters),
            'specialty' => $this->specialtyRows($filters),
            'patient' => $this->patientHistoryRows($filters),
            'absences' => $this->absenceRows($filters),
            'new_patients' => $this->patientRows($filters),
            default => collect($this->summary($filters))->map(fn ($value, $key) => [
                'indicador' => $key,
                'valor' => $value,
            ])->values(),
        };
    }

    private function patientHistoryRows(array $filters)
    {
        return $this->appointmentBase($filters)
            ->with(['patient', 'doctor.user', 'doctor.specialty', 'service'])
            ->latest('appointment_date')
            ->get()
            ->map(fn (Appointment $appointment) => [
                'paciente' => trim($appointment->patient?->first_name.' '.$appointment->patient?->last_name),
                'documento' => $appointment->patient?->document_number,
                'fecha' => $appointment->appointment_date?->format('d/m/Y'),
                'hora' => $this->formatTime($appointment->start_time),
                'medico' => $appointment->doctor?->user?->name,
                'especialidad' => $appointment->doctor?->specialty?->name,
                'motivo' => $appointment->service?->name ?? 'No registrado',
                'estado' => self::STATUS_LABELS[$appointment->status] ?? $appointment->status,
            ]);
    }

    private function absenceRows(array $filters)
    {
        return $this->appointmentBase($filters)
            ->with(['patient', 'doctor.user', 'doctor.specialty'])
            ->whereIn('status', ['CANCELLED', 'NO_SHOW'])
            ->latest('appointment_date')
            ->get()
            ->map(fn (Appointment $appointment) => [
                'paciente' => trim($appointment->patient?->first_name.' '.$appointment->patient?->last_name),
                'documento' => $appointment->patient?->document_number,
                'fecha' => $appointment->appointment_date?->format('d/m/Y'),
                'hora' => $this->formatTime($appointment->start_time),
                'medico' => $appointment->doctor?->user?->name,
                'especialidad' => $appointment->doctor?->specialty?->name,
                'estado' => self::STATUS_LABELS[$appointment->status] ?? $appointment->status,
                'motivo_cancelacion' => 'No registrado',
            ]);
    }

    private function printableHtml($rows, string $section): string
    {
        $title = 'Reporte '.str_replace('_', ' ', $section);
        $headers = $rows->isNotEmpty() ? array_keys($rows->first()) : [];
        $body = $rows->map(function (array $row) use ($headers) {
            $cells = collect($headers)
                ->map(fn ($header) => '<td>'.e((string) ($row[$header] ?? '')).'</td>')
                ->implode('');

            return '<tr>'.$cells.'</tr>';
        })->implode('');
        $head = collect($headers)->map(fn ($header) => '<th>'.e($header).'</th>')->implode('');

        return <<<HTML
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #0f172a; margin: 32px; }
        h1 { font-size: 22px; margin-bottom: 18px; }
        table { border-collapse: collapse; width: 100%; font-size: 12px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
        th { background: #e2e8f0; }
    </style>
</head>
<body>
    <h1>{$title}</h1>
    <table><thead><tr>{$head}</tr></thead><tbody>{$body}</tbody></table>
    <script>window.print()</script>
</body>
</html>
HTML;
    }

    private function excelHtml($rows, string $section): string
    {
        $headers = $rows->isNotEmpty() ? array_keys($rows->first()) : [];
        $head = collect($headers)->map(fn ($header) => '<th>'.e($header).'</th>')->implode('');
        $body = $rows->map(function (array $row) use ($headers) {
            return '<tr>'.collect($headers)->map(fn ($header) => '<td>'.e((string) ($row[$header] ?? '')).'</td>')->implode('').'</tr>';
        })->implode('');

        return '<html><meta charset="utf-8"><body><table><caption>Reporte '.e($section).'</caption><thead><tr>'.$head.'</tr></thead><tbody>'.$body.'</tbody></table></body></html>';
    }

    private function formatTime($value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('H:i');
        }

        if (is_string($value)) {
            return strlen($value) >= 5 ? substr($value, 0, 5) : $value;
        }

        return null;
    }
}
