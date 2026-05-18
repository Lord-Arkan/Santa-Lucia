<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Appointment;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('es');

        // fecha actual (hoy) usada para KPIs y proximas citas
        $today = Carbon::today();

        // Permitir navegar a una semana distinta pasando ?start_date=YYYY-MM-DD
        $startDateParam = $request->input('start_date');
        if ($startDateParam) {
            try {
                $start = Carbon::parse($startDateParam);
            } catch (\Exception $e) {
                $start = $today->copy();
            }
        } else {
            $start = $today->copy();
        }

        $end = $start->copy()->addDays(5);

        $overdueCount = Appointment::where('status', 'SCHEDULED')
            ->whereDate('appointment_date', '<', $today->toDateString())
            ->count();

        $pendingCount = Appointment::where('status', 'SCHEDULED')
            ->whereDate('appointment_date', '>=', $today->toDateString())
            ->count();

        // Conteo relativo a la semana actualmente mostrada (start..end)
        $totalWeek = Appointment::whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])->count();

        $summary = [
            ['id' => 'overdue', 'title' => 'Atrasadas', 'value' => $overdueCount],
            ['id' => 'pending', 'title' => 'Programadas', 'value' => $pendingCount],
            ['id' => 'week', 'title' => 'Esta semana', 'value' => $totalWeek],
        ];

        // Cargar citas en el rango mostrado (start..end)
        $appointments = Appointment::with('patient')
            ->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->get();

        $upcomingAppointments = Appointment::with('patient')
            ->whereDate('appointment_date', '>=', $today->toDateString())
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->limit(6)
            ->get()
            ->map(function ($a) {
                $date = Carbon::parse($a->appointment_date)->format('d/m');
                $time = $a->start_time instanceof \DateTimeInterface ? $a->start_time->format('H:i') : date('H:i', strtotime($a->start_time));
                $patient = $a->patient ? trim($a->patient->first_name . ' ' . $a->patient->last_name) : 'Paciente';
                return "{$date} - {$time} - {$patient}";
            })->toArray();

        // Build schedule grid for 6 days starting at $start
        $daysDates = [];
        $days = [];
        for ($i = 0; $i < 6; $i++) {
            $d = $start->copy()->addDays($i);
            $daysDates[] = $d;
            $days[] = ucfirst($d->translatedFormat('l'));
        }

        $times = $appointments->map(function ($a) {
            if ($a->start_time instanceof \DateTimeInterface) {
                return $a->start_time->format('H:i');
            }
            return date('H:i', strtotime($a->start_time));
        })->unique()->values();

        $rows = [];
        foreach ($times as $time) {
            $events = [];
            foreach ($daysDates as $d) {
                $found = $appointments->first(function ($a) use ($d, $time) {
                    $aDate = $a->appointment_date instanceof \DateTimeInterface ? $a->appointment_date->format('Y-m-d') : date('Y-m-d', strtotime($a->appointment_date));
                    $aTime = $a->start_time instanceof \DateTimeInterface ? $a->start_time->format('H:i') : date('H:i', strtotime($a->start_time));
                    return $aDate === $d->format('Y-m-d') && $aTime === $time;
                });

                $events[] = $found ? ($found->patient ? trim($found->patient->first_name . ' ' . $found->patient->last_name) : 'Paciente') : null;
            }

            $rows[] = ['time' => $time, 'events' => $events];
        }

        $schedule = ['days' => $days, 'rows' => $rows];
        // Add a human-readable range label for the displayed week (e.g. "Mayo 16 - Mayo 21")
        if (count($daysDates) > 0) {
            $startLabel = ucfirst($daysDates[0]->translatedFormat('F j'));
            $endLabel = ucfirst($daysDates[count($daysDates) - 1]->translatedFormat('F j'));
            $schedule['rangeLabel'] = "{$startLabel} - {$endLabel}";
            $schedule['start_date'] = $daysDates[0]->format('Y-m-d');
            $schedule['end_date'] = $daysDates[count($daysDates) - 1]->format('Y-m-d');
        } else {
            $schedule['rangeLabel'] = '';
            $schedule['start_date'] = null;
            $schedule['end_date'] = null;
        }

        return Inertia::render('Dashboard', [
            'summary' => $summary,
            'upcomingAppointments' => $upcomingAppointments,
            'schedule' => $schedule,
        ]);
    }
}
