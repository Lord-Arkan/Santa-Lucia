<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_service', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('service_id');
            $table->timestamps();

            $table->foreign('doctor_id')->references('doctor_id')->on('doctors')->onDelete('cascade');
            $table->foreign('service_id')->references('service_id')->on('services')->onDelete('cascade');

            $table->unique(['doctor_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_service');
    }
};
