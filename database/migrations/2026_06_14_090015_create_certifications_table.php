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
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('career_path_id')
                  ->nullable()
                  ->constrained('career_paths');

            $table->string('name', 200);
            $table->string('provider', 100)->nullable();
            $table->integer('recommended_semester')->nullable();
            $table->string('url', 300)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};
