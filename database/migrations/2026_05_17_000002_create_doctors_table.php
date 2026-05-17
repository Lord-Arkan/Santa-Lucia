<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->bigIncrements('doctor_id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->unsignedBigInteger('specialty_id');
            $table->string('license_number');
            $table->string('status')->default('activo');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('specialty_id')->references('specialty_id')->on('specialties')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
