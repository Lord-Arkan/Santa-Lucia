<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('SET GLOBAL event_scheduler = ON');

        DB::unprepared("
            CREATE EVENT IF NOT EXISTS expire_scheduled_appointments
            ON SCHEDULE EVERY 5 MINUTE
            DO
                UPDATE appointments
                SET status = 'EXPIRED', updated_at = CURRENT_TIMESTAMP
                WHERE status = 'SCHEDULED'
                  AND TIMESTAMP(appointment_date, start_time) < CURRENT_TIMESTAMP
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP EVENT IF EXISTS expire_scheduled_appointments');
        }
    }
};
