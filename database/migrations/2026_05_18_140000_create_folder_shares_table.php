<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('folder_shares', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('folder_id');
            $table->unsignedBigInteger('user_id');
            $table->string('permission', 16)->default('viewer'); // 'viewer' | 'editor'
            $table->unsignedBigInteger('shared_by')->nullable();
            $table->timestamps();

            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('shared_by')->references('id')->on('users')->onDelete('set null');

            $table->unique(['folder_id', 'user_id']);
            $table->index(['user_id', 'folder_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folder_shares');
    }
};
