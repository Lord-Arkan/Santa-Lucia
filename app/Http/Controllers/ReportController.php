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

    private const SECTION_TITLES = [
        'dashboard' => 'Resumen general',
        'period' => 'Citas por periodo',
        'doctor' => 'Citas por medico',
        'specialty' => 'Citas por especialidad',
        'patient' => 'Historial de pacientes',
        'absences' => 'Inasistencias y cancelaciones',
        'new_patients' => 'Pacientes nuevos',
    ];

    private const EXPORT_COLUMNS = [
        'dashboard' => [
            'indicador' => 'Indicador',
            'valor' => 'Valor',
        ],
        'period' => [
            'period' => 'Periodo',
            'total' => 'Total de citas',
            'pending' => 'Pendientes',
            'attended' => 'Atendidas',
            'cancelled' => 'Canceladas',
            'no_show' => 'No asistio',
        ],
        'doctor' => [
            'doctor' => 'Medico',
            'total' => 'Total de citas',
            'attended' => 'Atendidas',
            'pending' => 'Pendientes',
            'cancelled' => 'Canceladas',
            'no_show' => 'No asistio',
        ],
        'specialty' => [
            'specialty' => 'Especialidad',
            'total' => 'Total de citas',
            'attended' => 'Atendidas',
            'pending' => 'Pendientes',
            'cancelled' => 'Canceladas',
        ],
        'patient' => [
            'paciente' => 'Paciente',
            'documento' => 'Documento',
            'fecha' => 'Fecha',
            'hora' => 'Hora',
            'medico' => 'Medico',
            'especialidad' => 'Especialidad',
            'motivo' => 'Motivo',
            'estado' => 'Estado',
        ],
        'absences' => [
            'paciente' => 'Paciente',
            'documento' => 'Documento',
            'fecha' => 'Fecha',
            'hora' => 'Hora',
            'medico' => 'Medico',
            'especialidad' => 'Especialidad',
            'estado' => 'Estado',
            'motivo_cancelacion' => 'Motivo de cancelacion',
        ],
        'new_patients' => [
            'period' => 'Periodo',
            'total' => 'Pacientes nuevos',
        ],
    ];

    private const SUMMARY_LABELS = [
        'total_appointments' => 'Total de citas',
        'today_appointments' => 'Citas de hoy',
        'pending_appointments' => 'Citas pendientes',
        'attended_appointments' => 'Citas atendidas',
        'cancelled_appointments' => 'Citas canceladas',
        'attended_patients' => 'Pacientes atendidos',
        'top_doctor' => 'Medico con mas citas',
        'top_doctor_total' => 'Citas del medico destacado',
        'top_specialty' => 'Especialidad con mas citas',
        'top_specialty_total' => 'Citas de la especialidad destacada',
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

        $rows = $this->localizedExportRows($section, $filters);
        $filename = 'reporte-'.$this->sectionSlug($section).'-'.now()->format('Ymd-His');

        if ($format === 'pdf') {
            return response($this->printableHtml($rows, $section, $filters), 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
            ]);
        }

        if ($format === 'xlsx') {
            return response()->streamDownload(function () use ($rows, $section, $filters) {
                echo $this->excelHtml($rows, $section, $filters);
            }, $filename.'.xls', [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            ]);
        }

        return response()->streamDownload(function () use ($rows, $section) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            $headers = $rows->isNotEmpty() ? array_keys($rows->first()) : $this->exportHeaders($section);
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, collect($headers)->map(fn (string $header) => $row[$header] ?? '')->all());
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
            'doctor_id' => ['nullable', 'integer', 'exists:doctors,doctor_id'],
            'specialty_id' => ['nullable', 'integer', 'exists:specialties,specialty_id'],
            'patient_search' => ['nullable', 'string', 'max:255'],
        ]);

        $startDate = $validated['start_date'] ?? now()->subDays(30)->toDateString();
        $endDate = $validated['end_date'] ?? now()->toDateString();

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'group_by' => $this->resolveGroupBy($startDate, $endDate),
            'doctor_id' => $validated['doctor_id'] ?? '',
            'specialty_id' => $validated['specialty_id'] ?? '',
            'patient_search' => $validated['patient_search'] ?? '',
        ];
    }

    private function resolveGroupBy(string $startDate, string $endDate): string
    {
        $days = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;

        if ($days <= 31) {
            return 'day';
        }

        if ($days <= 366) {
            return 'month';
        }

        return 'year';
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
                'month' => ["strftime('%Y-%m', {$column})"],
                'year' => ["strftime('%Y', {$column})"],
                default => ["date({$column})"],
            };
        }

        return match ($groupBy) {
            'month' => ["DATE_FORMAT({$column}, '%Y-%m')"],
            'year' => ["YEAR({$column})"],
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
                'indicador' => self::SUMMARY_LABELS[$key] ?? $key,
                'valor' => $value,
            ])->values(),
        };
    }

    private function localizedExportRows(string $section, array $filters)
    {
        $columns = self::EXPORT_COLUMNS[$section] ?? self::EXPORT_COLUMNS['dashboard'];

        return $this->exportRows($section, $filters)
            ->map(function (array $row) use ($columns) {
                return collect($columns)
                    ->mapWithKeys(fn (string $label, string $key) => [$label => $row[$key] ?? ''])
                    ->all();
            });
    }

    private function exportHeaders(string $section): array
    {
        return array_values(self::EXPORT_COLUMNS[$section] ?? self::EXPORT_COLUMNS['dashboard']);
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

    private function printableHtml($rows, string $section, array $filters): string
    {
        $title = self::SECTION_TITLES[$section] ?? 'Reporte';
        $headers = $rows->isNotEmpty() ? array_keys($rows->first()) : $this->exportHeaders($section);
        $body = $rows->map(function (array $row) use ($headers) {
            $cells = collect($headers)
                ->map(fn ($header) => '<td>'.e($this->stringValue($row[$header] ?? '')).'</td>')
                ->implode('');

            return '<tr>'.$cells.'</tr>';
        })->implode('');
        $head = collect($headers)->map(fn ($header) => '<th>'.e($header).'</th>')->implode('');
        $range = e($this->dateRangeLabel($filters));
        $generatedAt = e(now()->format('d/m/Y H:i'));
        $empty = $rows->isEmpty() ? '<p class="empty">No hay datos para los filtros seleccionados.</p>' : '';

        return <<<HTML
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
    <style>
        @page { margin: 22mm 18mm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; color: #122033; margin: 0; background: #fff; }
        .report-header { border-bottom: 3px solid #1ecad3; padding-bottom: 16px; margin-bottom: 22px; }
        .brand { color: #0f766e; font-size: 11px; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; }
        h1 { color: #0f172a; font-size: 24px; margin: 8px 0 8px; }
        .meta { display: flex; gap: 18px; color: #526173; font-size: 12px; font-weight: 700; }
        table { border-collapse: collapse; width: 100%; font-size: 11px; }
        th { background: #122033; color: #fff; border: 1px solid #122033; padding: 9px; text-align: left; text-transform: uppercase; letter-spacing: .04em; }
        td { border: 1px solid #d7dee8; padding: 8px 9px; text-align: left; vertical-align: top; }
        tr:nth-child(even) td { background: #f6f9fc; }
        .empty { border: 1px dashed #cbd5e1; border-radius: 12px; color: #64748b; padding: 18px; text-align: center; }
        .footer { color: #64748b; font-size: 10px; margin-top: 22px; }
        @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>
<body>
    <header class="report-header">
        <div class="brand">Clinica Santa Lucia</div>
        <h1>{$title}</h1>
        <div class="meta">
            <span>{$range}</span>
            <span>Generado: {$generatedAt}</span>
        </div>
    </header>
    {$empty}
    <table><thead><tr>{$head}</tr></thead><tbody>{$body}</tbody></table>
    <p class="footer">Documento generado automaticamente por el sistema de reportes de Santa Lucia.</p>
    <script>window.print()</script>
</body>
</html>
HTML;
    }

    private function excelHtml($rows, string $section, array $filters): string
    {
        $title = self::SECTION_TITLES[$section] ?? 'Reporte';
        $headers = $rows->isNotEmpty() ? array_keys($rows->first()) : $this->exportHeaders($section);
        $colspan = max(count($headers), 1);
        $head = collect($headers)->map(fn ($header) => '<th style="background:#122033;color:#ffffff;border:1px solid #122033;padding:8px;text-align:left;">'.e($header).'</th>')->implode('');
        $body = $rows->map(function (array $row) use ($headers) {
            return '<tr>'.collect($headers)->map(fn ($header) => '<td style="border:1px solid #d7dee8;padding:7px;">'.e($this->stringValue($row[$header] ?? '')).'</td>')->implode('').'</tr>';
        })->implode('');
        $range = e($this->dateRangeLabel($filters));
        $generatedAt = e(now()->format('d/m/Y H:i'));

        return '<html><meta charset="utf-8"><body>'.
            '<table style="border-collapse:collapse;font-family:Arial,sans-serif;font-size:12px;">'.
            '<tr><td colspan="'.$colspan.'" style="font-size:18px;font-weight:800;color:#0f172a;padding:10px 8px;">'.e($title).'</td></tr>'.
            '<tr><td colspan="'.$colspan.'" style="font-weight:700;color:#0f766e;padding:6px 8px;">Clinica Santa Lucia</td></tr>'.
            '<tr><td colspan="'.$colspan.'" style="color:#526173;padding:6px 8px;">'.$range.' | Generado: '.$generatedAt.'</td></tr>'.
            '<tr>'.$head.'</tr>'.$body.
            '</table></body></html>';
    }

    private function dateRangeLabel(array $filters): string
    {
        $start = Carbon::parse($filters['start_date'])->format('d/m/Y');
        $end = Carbon::parse($filters['end_date'])->format('d/m/Y');

        return "Periodo: {$start} - {$end}";
    }

    private function sectionSlug(string $section): string
    {
        return [
            'dashboard' => 'resumen-general',
            'period' => 'citas-por-periodo',
            'doctor' => 'citas-por-medico',
            'specialty' => 'citas-por-especialidad',
            'patient' => 'historial-pacientes',
            'absences' => 'inasistencias',
            'new_patients' => 'pacientes-nuevos',
        ][$section] ?? str_replace('_', '-', $section);
    }

    private function stringValue($value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y H:i');
        }

        if (is_bool($value)) {
            return $value ? 'Si' : 'No';
        }

        return (string) $value;
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
