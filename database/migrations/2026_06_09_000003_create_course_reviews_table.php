<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lecturer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('student_id', 50)->index();
            $table->unsignedTinyInteger('difficulty');
            $table->unsignedTinyInteger('teaching_rating');
            $table->text('tips')->nullable();
            $table->text('uts_strategy')->nullable();
            $table->text('uas_strategy')->nullable();
            $table->unsignedTinyInteger('semester_taken')->nullable();
            $table->string('academic_year', 10)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_reviews');
    }
};
