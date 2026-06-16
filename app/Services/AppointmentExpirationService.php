<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentExpirationService
{
    /**
     * MySQL local keeps using the scheduled EVENT.
     * SQLite production uses this Laravel-side sync as the fallback.
     */
    public function syncExpiredScheduledAppointments(): int
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            return 0;
        }

        $now = Carbon::now(config('app.timezone'));
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');

        return DB::transaction(function () use ($now, $today, $currentTime): int {
            return Appointment::query()
                ->where('status', 'SCHEDULED')
                ->where(function ($query) use ($today, $currentTime) {
                    $query->where('appointment_date', '<', $today)
                        ->orWhere(function ($sameDayQuery) use ($today, $currentTime) {
                            $sameDayQuery->where('appointment_date', $today)
                                ->where('start_time', '<', $currentTime);
                        });
                })
                ->update([
                    'status' => 'EXPIRED',
                    'updated_at' => $now,
                ]);
        });
    }
}
