<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->bigIncrements('service_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('duration_minutes')->default(0);
            $table->string('status')->default('activo');
            $table->timestamps();

            $table->unique(['name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
