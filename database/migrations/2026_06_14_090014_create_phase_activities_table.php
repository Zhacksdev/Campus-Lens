<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('phase_activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('phase_id')
                  ->constrained('roadmap_phases')
                  ->onDelete('cascade');

            $table->string('type', 50);
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->integer('priority')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phase_activities');
    }
};
