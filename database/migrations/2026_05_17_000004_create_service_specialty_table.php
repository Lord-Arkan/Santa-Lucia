<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_specialty', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('specialty_id');
            $table->timestamps();

            $table->foreign('service_id')->references('service_id')->on('services')->onDelete('cascade');
            $table->foreign('specialty_id')->references('specialty_id')->on('specialties')->onDelete('restrict');

            $table->unique(['service_id', 'specialty_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_specialty');
    }
};
