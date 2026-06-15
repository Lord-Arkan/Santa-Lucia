<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('rol', 'doctor')
            ->select(['id', 'module_permissions'])
            ->orderBy('id')
            ->each(function (object $user): void {
                $permissions = json_decode($user->module_permissions ?? '[]', true) ?: [];

                DB::table('users')->where('id', $user->id)->update([
                    'module_permissions' => json_encode(array_values(array_unique([
                        ...$permissions,
                        'patients',
                        'history',
                    ]))),
                ]);
            });
    }

    public function down(): void
    {
        //
    }
};
