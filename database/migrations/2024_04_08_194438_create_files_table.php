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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('depositor_name');
            $table->string('document_id');
            $table->string('email');
            $table->string('phone_number');
            $table->string('file_title');
            $table->string('file_format');
            $table->date('year_created');
            $table->date('year_deposit');
            $table->string('document_file');
            $table->string('unit');
            $table->boolean('is_approve')->default(false);
            $table->timestamps();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
