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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('instructor_name');
            $table->string('course_code');
            $table->string('course_title');
            $table->string('faculty');
            $table->string('semester');
            $table->string('academic_year');
            $table->string('exams_type');
            $table->date('exam_date');
            $table->string('exam_format');
            $table->string('duration');
            $table->string('exam_document');
            $table->string('answer_key')->nullable();
            $table->string('special_instruction')->nullable();
            $table->string('tags')->nullable();
            $table->boolean('consent_box')->default(true);
            $table->boolean('is_approve')->default(false);
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
