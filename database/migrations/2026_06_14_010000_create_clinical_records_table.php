<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->string('type', 100);
            $table->text('content');
            $table->timestamps();

            $table->foreign('patient_id')->references('patient_id')->on('patients')->cascadeOnDelete();
            $table->foreign('doctor_id')->references('doctor_id')->on('doctors')->restrictOnDelete();
            $table->index(['patient_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_records');
    }
};
